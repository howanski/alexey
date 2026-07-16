<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\AssistantCall;
use App\Entity\User;
use App\Message\AsyncJob;
use App\Model\AssistantMessageBag;
use App\Model\AssistantMessageDTO;
use App\Model\AssistantSettings;
use App\Repository\AssistantCallRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\AI\Agent\Agent;
use Symfony\AI\Platform\Bridge\Generic\Factory;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Platform;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class AssistantService
{
    public const MAX_PROCESSING_TIME = 7200; // Two hours for slow local processing
    private const BASE_URL = 'baseUrl';
    private const API_KEY = 'apiKey';
    private const MODEL = 'model';

    private array $agents = [];

    public function __construct(
        private AssistantCallRepository $assistantCallRepository,
        private EntityManagerInterface $em,
        private SimpleSettingsService $simpleSettingsService,
        private MessageBusInterface $bus,
    ) {
    }

    public function getDefaultOptionsForUser(UserInterface $user): array
    {
        $settings = new AssistantSettings();
        $settings->selfConfigure($this->simpleSettingsService, $user);
        return [
            self::BASE_URL => $settings->getBaseUrl(),
            self::API_KEY => $settings->getApiKey(),
            self::MODEL => $settings->getModel(),
        ];
    }

    public function quickMessage(
        UserInterface $user,
        string $message,
        string $baseUrl,
        string $model,
        string $apiKey,
    ): ResultInterface {
        $options = [
            self::BASE_URL => $baseUrl,
            self::API_KEY => $apiKey,
            self::MODEL => $model,
        ];

        $agent = $this->getDefaultAgent($user, $options);

        $messageBag = new AssistantMessageBag();

        $messageBag->addMessage(Message::ofUser($message));

        return $agent->call(messages: $messageBag->getBag());
    }

    public function sendMessage(User $user, AssistantMessageDTO $dto): AssistantCall
    {
        $call = AssistantCall::fromMessageDTO($user, $dto);
        $rootId = $dto->getRootId();
        if (!empty($rootId)) {
            $root = $this->assistantCallRepository->find($rootId);
            if ($root instanceof AssistantCall) {
                $call->setRoot($root);
                $call->setParent($root->getLastChild());
            }
        }
        $this->em->persist($call);
        $this->em->flush();
        $this->em->refresh($call);
        $this->bus->dispatch(new AsyncJob(
            jobType: AsyncJob::TYPE_PROCESS_ASSISTANT_CALLS,
            payload: [
                'id' => $call->getId(),
            ],
        ));
        return $call;
    }

    public function getUserChats(UserInterface $user): array
    {
        return $this->assistantCallRepository->getUserChats($user);
    }

    public function getModelChoices(UserInterface $user): array
    {
        $defaultModel = $this->getDefaultOptionsForUser($user)[self::MODEL];
        // Basic version, this will be expanded to support multiple models
        $choices = [
            $defaultModel => $defaultModel,
        ];
        return $choices;
    }

    public function getDefaultAgent(UserInterface $user, array $options): Agent
    {
        return $this->getAgent($user, $options);
    }

    private function getAgent(UserInterface $user, array $options): Agent
    {
        $userId = $user->getUserIdentifier();
        if (
            !empty($this->agents[$userId][$options[self::MODEL]])
            && $this->agents[$userId][$options[self::MODEL]] instanceof Agent
        ) {
            return $this->agents[$userId][$options[self::MODEL]];
        }

        $this->agents[$userId][$options[self::MODEL]] = new Agent(
            platform: $this->getDefaultPlatform($options),
            model: $options[self::MODEL],
        );
        return $this->agents[$userId][$options[self::MODEL]];
    }

    private function getDefaultPlatform(array $options): Platform
    {
        $httpClient = HttpClient::create(['timeout' => AssistantService::MAX_PROCESSING_TIME]);
        return Factory::createPlatform(
            baseUrl: $options[self::BASE_URL],
            apiKey: $options[self::API_KEY],
            httpClient: $httpClient,
        );
    }
}
