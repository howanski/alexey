<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\AssistantCall;
use App\Entity\AssistantRecurringMessage;
use App\Message\AsyncJob;
use App\Model\AssistantMessageBag;
use App\Repository\AssistantCallRepository;
use App\Repository\AssistantRecurringMessageRepository;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\AI\Platform\Message\Message;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

final class AssistantCallProcessor
{
    public function __construct(
        private AssistantCallRepository $assistantCallRepository,
        private MessageBusInterface $bus,
        private EntityManagerInterface $em,
        private AssistantService $service,
        private AssistantRecurringMessageRepository $assistantRecurringMessageRepository,
    ) {
    }

    public function processCallById(int $id): void
    {
        if (true === $this->isProcessingPipelineFree()) {
            // Atomic claim — prevents TOCTOU by transitioning status in a single SQL operation.
            $claimed = (int) $this->em->createQueryBuilder()
                ->update(AssistantCall::class, 'c')
                ->set('c.status', ':processing')
                ->andWhere('c.id = :id')
                ->andWhere('c.status IN (:statuses)')
                ->setParameters([
                    ':processing' => AssistantCall::STATUS_PROCESSING,
                    ':id' => $id,
                    ':statuses' => [AssistantCall::STATUS_READY_TO_PROCESS],
                ])
                ->getQuery()
                ->execute();

            if (!($claimed === 1)) {
                // Another job already claimed this call, or its status changed.
                return;
            }

            $entity = $this->assistantCallRepository->find($id);
            if (!$entity instanceof AssistantCall) {
                return;
            }

            $this->em->refresh($entity);
            if (!($entity->getStatus() === AssistantCall::STATUS_PROCESSING)) {
                return;
            }

            try {
                $this->processSendChat($entity);
                $entity->setStatus(AssistantCall::STATUS_DONE);
            } catch (Exception $e) {
                //TODO: logging?
                $entity->setStatus(AssistantCall::STATUS_ERROR);
            } finally {
                $this->em->flush();
            }

            $this->scheduleMaintenance();
        } else {
            $this->queueProcessingJob(id: (int) $id);
        }
    }

    public function runMaintenance(): void
    {
        if ($this->isProcessingPipelineFree()) {
            $entity = $this->assistantCallRepository->findOldestWithStatus(AssistantCall::STATUS_READY_TO_PROCESS);
            if ($entity instanceof AssistantCall) {
                $this->queueProcessingJob((int) $entity->getId(), 1);
                $this->scheduleMaintenance();
                return;
            }
            $entity = $this->assistantCallRepository->findOldestWithStatus(AssistantCall::STATUS_ERROR);
            if ($entity instanceof AssistantCall) {
                // TODO: some sort of handling?
                $entity->setStatus(AssistantCall::STATUS_READY_TO_PROCESS);
                $this->em->flush();
                $this->scheduleMaintenance();
                return;
            }
            $entity = $this->assistantCallRepository->findOldestWithStatus(AssistantCall::STATUS_TO_REDO);
            if ($entity instanceof AssistantCall) {
                if (!($entity->getId() === $entity->getLastChild()->getId())) {
                    $this->removeLastNode($entity);
                } else {
                    $entity->setStatus(AssistantCall::STATUS_READY_TO_PROCESS);
                }
                $this->em->flush();
                $this->scheduleMaintenance(0);
                return;
            }
        } else {
            $entity = $this->assistantCallRepository->findOldestWithStatus(AssistantCall::STATUS_PROCESSING);
            if ($entity instanceof AssistantCall) {
                $lastChange = $entity->getLastStatusChange();
                $now = new Carbon('now');
                $now = $now->subSeconds(AssistantService::MAX_PROCESSING_TIME + 30);
                if ($now->isAfter($lastChange)) {
                    $entity->setStatus(AssistantCall::STATUS_ERROR);
                    $this->em->flush();
                    $this->scheduleMaintenance();
                    return;
                }
            }
        }

        $trash = $this->assistantCallRepository->findBy([
            'type' => AssistantCall::TYPE_TRASH
        ]);
        if (!empty($trash)) {
            foreach ($trash as $trashItem) {
                $this->removeLastNode($trashItem);
                $this->em->flush();
                $this->scheduleMaintenance();
                return;
            }
        }
    }

    private function scheduleMaintenance(int $delaySeconds = 2)
    {
        if ($delaySeconds === 0) {
            $delayStamp = new DelayStamp(1);
        } else {
            $delayStamp = new DelayStamp(1000 * $delaySeconds);
        }
        $message = new AsyncJob(
            jobType: AsyncJob::TYPE_PROCESS_ASSISTANT_CALLS,
            payload: [],
        );

        $this->bus->dispatch(
            message: $message,
            stamps: [
                $delayStamp,
            ]
        );
    }

    private function removeLastNode(AssistantCall $call): void
    {
        $this->em->remove($call->getLastChild());
    }

    private function isProcessingPipelineFree(): bool
    {
        return 0 === $this->assistantCallRepository->countCallsWithStatus(AssistantCall::STATUS_PROCESSING);
    }

    private function queueProcessingJob(int $id, int $delaySeconds = 30): void
    {
        $delayStamp = new DelayStamp(1000 * $delaySeconds);
        $message = new AsyncJob(
            jobType: AsyncJob::TYPE_PROCESS_ASSISTANT_CALLS,
            payload: [
                'id' => $id,
            ],
        );

        $this->bus->dispatch(
            message: $message,
            stamps: [
                $delayStamp,
            ]
        );
    }

    private function processSendChat(AssistantCall $entity): void
    {
        $user = $entity->getUser();
        $messageBag = new AssistantMessageBag();

        $defaultSystemMessage = $entity->getSystemMessage();
        if (is_null($defaultSystemMessage) || !($defaultSystemMessage instanceof AssistantRecurringMessage)) {
            $defaultSystemMessage = $this->assistantRecurringMessageRepository->getDefaultSystemMessage($user);
        }

        if ($defaultSystemMessage instanceof AssistantRecurringMessage) {
            $systemMessage = $defaultSystemMessage->getMessage();
            if (!empty($systemMessage)) {
                $messageBag->addMessage(Message::forSystem($systemMessage));
            }
        }

        $fullMessageHistory = $entity->getRootEntity()->getMessagesTimeline();
        $lastKey = array_key_last($fullMessageHistory);
        foreach ($fullMessageHistory as $key => $historySlice) {
            $messageBag->addMessage(Message::ofUser($historySlice['request']));
            if (!($key === $lastKey) && !empty($historySlice['response'])) {
                $messageBag->addMessage(Message::ofAssistant($historySlice['response']));
            }
        }
        $options = $this->service->getDefaultOptionsForUser($user);

        $tools = $entity->getTools();
        $agent = $this->service->getDefaultAgent($user, $options, $tools);
        $result = $agent->call(messages: $messageBag->getBag());

        $entity->setAssistantResponse($result->getContent());
        $entity->setMetadata($result->getMetadata()->jsonSerialize());
    }
}
