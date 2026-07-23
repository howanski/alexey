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

    private string $baseUrl = '';
    private string $apiKey = '';
    private int $modelId = 0;

    /**
     * @deprecated to be removed on next code cleanup
     * it keeps the model name, I need to stop exposing it
     * and use entity id (or whole entity???)
     * 
     * Generally this class is getting rotten with workaround code, might need rethinking
     * how to do it better
     */
    private string $model = '';
    private string $systemMessage = '';

    public function selfConfigure(SimpleSettingsService $simpleSettingsService, UserInterface $user): void
    {
        $settingsArray = $simpleSettingsService->getSettings(
            keys: [
                self::BASE_URL,
                self::API_KEY,
            ],
            user: $user,
        );
        $this->setBaseUrl(strval($settingsArray[self::BASE_URL]));
        $this->setApiKey(strval($settingsArray[self::API_KEY]));

        /**
         * @var AssistantRecurringMessageRepository
         */
        $repo = $simpleSettingsService->getManager()->getRepository(AssistantRecurringMessage::class);

        $defaultMessage = $repo->getDefaultSystemMessage($user);
        if ($defaultMessage instanceof AssistantRecurringMessage) {
            $this->setSystemMessage($defaultMessage->getMessage());
            $this->model = ($defaultMessage->getModel());
            $this->setModelId($defaultMessage->getId());
        }
    }

    public function selfPersist(SimpleSettingsService $simpleSettingsService, User $user): void
    {
        $simpleSettingsService->saveSettings(
            settings: [
                self::BASE_URL => $this->getBaseUrl(),
                self::API_KEY => $this->getApiKey(),
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
        $defaultMessage->setModel($this->model);
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

    public function getModelId(): int
    {
        return $this->modelId;
    }

    private function setModelId(int $modelId): void
    {
        $this->modelId = $modelId;
    }

    /**
     * @deprecated to be removed on next code cleanup
     */
    public function getModel(): string
    {
        return $this->model;
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
