<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\AssistantRecurringMessage;
use App\Entity\User;
use App\Repository\AssistantRecurringMessageRepository;
use App\Service\SimpleSettingsService;
use Symfony\Component\Security\Core\User\UserInterface;

final class AssistantSettings
{
    private const BASE_URL = 'ASSISTANT_DEFAULT_URL';
    private const API_KEY = 'ASSISTANT_DEFAULT_API_KEY';
    private const MODEL = 'ASSISTANT_DEFAULT_MODEL';

    private string $baseUrl = '';
    private string $apiKey = '';
    private string $model = '';
    private string $systemMessage = '';

    public function selfConfigure(SimpleSettingsService $simpleSettingsService, UserInterface $user): void
    {
        $settingsArray = $simpleSettingsService->getSettings(
            keys: [
                self::BASE_URL,
                self::API_KEY,
                self::MODEL,
            ],
            user: $user,
        );
        $this->setBaseUrl(strval($settingsArray[self::BASE_URL]));
        $this->setApiKey(strval($settingsArray[self::API_KEY]));
        $this->setModel(strval($settingsArray[self::MODEL]));

        /**
         * @var AssistantRecurringMessageRepository
         */
        $repo = $simpleSettingsService->getManager()->getRepository(AssistantRecurringMessage::class);

        $defaultMessage = $repo->getDefaultSystemMessage($user);
        if ($defaultMessage instanceof AssistantRecurringMessage) {
            $this->setSystemMessage($defaultMessage->getMessage());
        }
    }

    public function selfPersist(SimpleSettingsService $simpleSettingsService, User $user): void
    {
        $simpleSettingsService->saveSettings(
            settings: [
                self::BASE_URL => $this->getBaseUrl(),
                self::API_KEY => $this->getApiKey(),
                self::MODEL => $this->getModel(),
            ],
            user: $user,
            flush: false
        );

        $em = $simpleSettingsService->getManager();

        /**
         * @var AssistantRecurringMessageRepository
         */
        $repo = $em->getRepository(AssistantRecurringMessage::class);

        $defaultMessage = $repo->getDefaultSystemMessage($user);
        if (!($defaultMessage instanceof AssistantRecurringMessage)) {
            $defaultMessage = new AssistantRecurringMessage();
            $defaultMessage->setPriority(0);
            $defaultMessage->setUser($user);
            $defaultMessage->setType(AssistantRecurringMessage::TYPE_SYSTEM_MESSAGE);
            $em->persist($defaultMessage);
        }
        $defaultMessage->setMessage($this->getSystemMessage());
    }

    public function isConfigured(): bool
    {
        return !empty($this->baseUrl) && !empty($this->model);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): void
    {
        $this->apiKey = strval($apiKey);
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function getSystemMessage(): string
    {
        return $this->systemMessage;
    }

    public function setSystemMessage(?string $systemMessage): void
    {
        $this->systemMessage = strval($systemMessage);
    }
}
