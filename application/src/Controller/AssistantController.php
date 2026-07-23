<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\AssistantMessageType;
use App\Form\AssistantSettingsType;
use App\Model\AssistantMessageDTO;
use App\Model\AssistantSettings;
use App\Service\AlexeyTranslator;
use App\Service\AssistantService;
use App\Service\SimpleSettingsService;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/assistant')]
final class AssistantController extends AlexeyAbstractController
{
    #[Route('/', name: 'assistant_index', methods: ['GET', 'POST'])]
    public function index(
        AlexeyTranslator $translator,
        AssistantService $service,
        Request $request,
        SimpleSettingsService $simpleSettingsService,
    ): Response {
        $user = $this->alexeyUser();
        $settings = new AssistantSettings();
        $settings->selfConfigure($simpleSettingsService, $user);

        if (false === $settings->isConfigured()) {
            return $this->redirectToRoute('assistant_config');
        }

        $dto = new AssistantMessageDTO();
        $dto->setModelId($settings->getModelId());

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
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('sent'));
            return $this->redirectToRoute('assistant_chat_view', ['id' => $call->getId()]);
        }

        return $this->render(
            'assistant/index.html.twig',
            [
                'chats' => $service->getUserChats($user),
                'form' => $form,
            ],
        );
    }

    #[Route('/config', name: 'assistant_config', methods: ['GET', 'POST'])]
    public function configure(
        AlexeyTranslator $translator,
        AssistantService $service,
        LoggerInterface $loggerInterface,
        Request $request,
        SimpleSettingsService $simpleSettingsService,
    ): Response {
        $user = $this->alexeyUser();
        $settings = new AssistantSettings();
        $settings->selfConfigure($simpleSettingsService, $user);
        $form = $this->createForm(
            AssistantSettingsType::class,
            $settings
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->beginTransaction();
            $settings->selfPersist($simpleSettingsService, $user);
            try {
                $result = $service->quickMessage(
                    user: $user,
                    baseUrl: $settings->getBaseUrl(),
                    model: $settings->getModel(),
                    apiKey: $settings->getApiKey(),
                    message: 'Only respond with "OK"',
                );
                $result->getContent();

                $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));
                $this->em->flush();
                $this->em->commit();
                return $this->redirectToRoute('assistant_index', [], Response::HTTP_SEE_OTHER);
            } catch (Exception $e) {
                $loggerInterface->warning(sprintf('Assistant config validation failed: %s', $e->getMessage()));
                $this->em->rollback();
                $this->addFlash(type: 'nord11', message: $translator->translateFlash('wrong_settings', 'assistant'));
                return $this->render('assistant/settings.html.twig', [
                    'form' => $form,
                ]);
            }
        }

        return $this->render('assistant/settings.html.twig', [
            'form' => $form,
        ]);
    }
}
