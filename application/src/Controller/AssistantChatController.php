<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AssistantCall;
use App\Form\AssistantMessageType;
use App\Message\AsyncJob;
use App\Model\AssistantChat;
use App\Model\AssistantMessageDTO;
use App\Model\AssistantSettings;
use App\Service\AlexeyTranslator;
use App\Service\AssistantService;
use App\Service\SimpleSettingsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/assistant/chat')]
final class AssistantChatController extends AlexeyAbstractController
{
    #[Route('/view/{id}', name: 'assistant_chat_view', methods: ['GET', 'POST'])]
    public function viewChat(
        AssistantService $service,
        int $id,
        Request $request,
        SimpleSettingsService $simpleSettingsService,
    ): Response {
        $call = $this->fetchEntityById(className: AssistantCall::class, id: $id);
        if (!($call instanceof AssistantCall)) {
            return $this->redirectToRoute('assistant_index');
        }
        $user = $this->alexeyUser();
        if (!($call->getUser()->getUserIdentifier() === $user->getUserIdentifier())) {
            return $this->redirectToRoute('assistant_index');
        }

        $root = $call->getRoot();
        if ($root instanceof AssistantCall && !($root->getId() === $call->getId())) {
            return $this->redirectToRoute('assistant_chat_view', ['id' => $root->getId()]);
        }

        if (!($call->getType() === AssistantCall::TYPE_CHAT)) {
            return $this->redirectToRoute('assistant_index');
        }

        $chat = AssistantChat::fromCall($call);

        $settings = new AssistantSettings();
        $settings->selfConfigure($simpleSettingsService, $user);

        if (false === $settings->isConfigured()) {
            return $this->redirectToRoute('assistant_config');
        }

        $dto = new AssistantMessageDTO();
        $dto->setModelId($call->getLastChild()->getSystemMessage()->getId());
        $dto->setTools($call->getLastChild()->getTools());
        $dto->setRootId($id);

        $form = $this->createForm(
            AssistantMessageType::class,
            $dto,
            [
                'model_choices' => $service->getModelChoices($user)
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $call = $service->sendMessage($user, $dto);
            return $this->redirectToRoute('assistant_chat_view', ['id' => $id]);
        }
        return $this->render(
            'assistant/chat_view.html.twig',
            [
                'chat' => $chat,
                'form' => $form,
                'id' => $id,
            ],
        );
    }

    #[Route('/delete/{id}', name: 'assistant_chat_delete', methods: ['GET'])]
    public function deleteChat(
        AlexeyTranslator $translator,
        int $id,
        MessageBusInterface $bus,
    ): Response {
        $call = $this->fetchEntityById(className: AssistantCall::class, id: $id);
        if (!($call instanceof AssistantCall)) {
            $this->addFlash(type: 'nord11', message: $translator->translateFlash('delete_forbidden'));
            return $this->redirectToRoute('assistant_index');
        }
        $user = $this->alexeyUser();
        if (!($call->getUser()->getUserIdentifier() === $user->getUserIdentifier())) {
            $this->addFlash(type: 'nord11', message: $translator->translateFlash('delete_forbidden'));
            return $this->redirectToRoute('assistant_index');
        }

        $call->setType(AssistantCall::TYPE_TRASH);
        $call->setStatus(AssistantCall::STATUS_PROCESSING);
        $this->em->flush();

        $bus->dispatch(new AsyncJob(
            jobType: AsyncJob::TYPE_PROCESS_ASSISTANT_CALLS,
            payload: [],
        ));

        $root = $call->getRoot();
        if ($root instanceof AssistantCall && !($root->getId() === $id)) {
            return $this->redirectToRoute('assistant_chat_view', ['id' => $root->getId()]);
        }

        $this->addFlash(type: 'nord14', message: $translator->translateFlash('deleted'));


        return $this->redirectToRoute('assistant_index');
    }

    #[Route('/ajax/is-processed/{id}', name: 'assistant_ajax_is_processed', methods: ['GET'])]
    public function ajaxCheckIsProcessed(
        int $id,
    ) {
        $call = $this->fetchEntityById(className: AssistantCall::class, id: $id);
        if ($call instanceof AssistantCall) {
            if (!($call->getStatus() === AssistantCall::STATUS_DONE)) {
                return $this->json([
                    'result' => false,
                ]);
            }
        }
        return $this->json([
            'result' => true,
        ]);
    }

    #[Route('/ajax/redo/{id}', name: 'assistant_ajax_redo', methods: ['GET'])]
    public function ajaxRedo(
        int $id,
        MessageBusInterface $bus,
    ) {
        $call = $this->fetchEntityById(className: AssistantCall::class, id: $id);
        if ($call instanceof AssistantCall) {
            $user = $this->alexeyUser();
            if ($call->getUser()->getUserIdentifier() === $user->getUserIdentifier()) {
                $call->setStatus(AssistantCall::STATUS_TO_REDO);
                $this->em->flush();
                $bus->dispatch(new AsyncJob(
                    jobType: AsyncJob::TYPE_PROCESS_ASSISTANT_CALLS,
                    payload: [],
                ));
            }
            return $this->redirectToRoute('assistant_chat_view', ['id' => $call->getRootEntity()->getId()]);
        }
        return $this->redirectToRoute('assistant_index');
    }
}
