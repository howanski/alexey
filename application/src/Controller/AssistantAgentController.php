<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AssistantRecurringMessage;
use App\Entity\User;
use App\Form\AssistantAgentType;
use App\Repository\AssistantCallRepository;
use App\Model\AssistantSettings;
use App\Repository\AssistantRecurringMessageRepository;
use App\Service\AlexeyTranslator;
use App\Service\AssistantService;
use App\Service\SimpleSettingsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/assistant/agent')]
final class AssistantAgentController extends AlexeyAbstractController
{
    #[Route('/list', name: 'assistant_agent_list', methods: ['GET'])]
    public function list(
        AssistantService $service,
        SimpleSettingsService $simpleSettingsService,
    ): Response {
        $user = $this->alexeyUser();
        if (!($user instanceof User)) {
            return $this->banishToLoginPage();
        }
        $settings = new AssistantSettings();
        $settings->selfConfigure($simpleSettingsService, $user);

        if (false === $settings->isConfigured()) {
            return $this->redirectToRoute('assistant_config');
        }

        return $this->render(
            'assistant/agent_list.html.twig',
            [
                'agents' => $service->getAvailableAgents($user),
            ],
        );
    }

    #[Route('/new', name: 'assistant_agent_new', methods: ['GET', 'POST'])]
    public function new(
        AlexeyTranslator $translator,
        AssistantRecurringMessageRepository $repository,
        Request $request,
    ): Response {
        $user = $this->alexeyUser();
        if (!($user instanceof User)) {
            return $this->banishToLoginPage();
        }
        $availablePrioritySlot = $repository->getNextFreePrioritySlot(
            $user,
            AssistantRecurringMessage::TYPE_SYSTEM_MESSAGE,
        );
        $agent = new AssistantRecurringMessage();
        $agent->setType(AssistantRecurringMessage::TYPE_SYSTEM_MESSAGE);
        $agent->setPriority($availablePrioritySlot);
        $agent->setUser($user);

        $form = $this->createForm(AssistantAgentType::class, $agent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($agent);
            $this->em->flush();
            $this->flashSuccess($translator->translateFlash('saved'));
            return $this->redirectToRoute('assistant_agent_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('assistant/agent_new.html.twig', [
            'agent' => $agent,
            'form' => $form,
        ]);
    }

    #[Route('/edit/{id}', name: 'assistant_agent_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        int $id,
        AlexeyTranslator $translator,
    ): Response {
        $user = $this->alexeyUser();
        $agent = $this->fetchEntityById(AssistantRecurringMessage::class, $id);

        if (!($agent instanceof AssistantRecurringMessage)) {
            return $this->redirectToRoute('assistant_agent_list', [], Response::HTTP_SEE_OTHER);
        }

        if (!($agent->getUser() === $user)) {
            return $this->redirectToRoute('assistant_agent_list', [], Response::HTTP_SEE_OTHER);
        }

        if (!($agent->getType() === AssistantRecurringMessage::TYPE_SYSTEM_MESSAGE)) {
            return $this->redirectToRoute('assistant_agent_list', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(AssistantAgentType::class, $agent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($agent);
            $this->em->flush();
            $this->flashSuccess($translator->translateFlash('saved'));
            return $this->redirectToRoute('assistant_agent_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('assistant/agent_new.html.twig', [
            'agent' => $agent,
            'form' => $form,
            'breadcrumb_label' => 'edit',
        ]);
    }

    #[Route('/delete/{id}', name: 'assistant_agent_delete', methods: ['POST'])]
    public function delete(
        AlexeyTranslator $translator,
        AssistantCallRepository $assistantCallRepository,
        int $id,
        Request $request,
    ): Response {
        $user = $this->alexeyUser();

        $agent = $this->fetchEntityById(AssistantRecurringMessage::class, $id);

        if (!($agent instanceof AssistantRecurringMessage)) {
            return $this->redirectToRoute('assistant_agent_list', [], Response::HTTP_SEE_OTHER);
        }

        if (!($agent->getUser() === $user)) {
            return $this->redirectToRoute('assistant_agent_list', [], Response::HTTP_SEE_OTHER);
        }

        if (!($agent->getType() === AssistantRecurringMessage::TYPE_SYSTEM_MESSAGE)) {
            return $this->redirectToRoute('assistant_agent_list', [], Response::HTTP_SEE_OTHER);
        }

        if (!$this->isCsrfTokenValid('delete_agent_' . $agent->getId(), (string) $request->request->get('_token'))) {
            $this->flashError($translator->translateFlash('delete_forbidden'));
            return $this->redirectToRoute('assistant_agent_list', [], Response::HTTP_SEE_OTHER);
        }

        if ($agent->isDefault()) {
            $this->flashError($translator->translateFlash('delete_forbidden'));
            return $this->redirectToRoute('assistant_agent_list', [], Response::HTTP_SEE_OTHER);
        }

        $deletedPriority = $agent->getPriority();

        // Delete all chats related to this agent
        $calls = $assistantCallRepository->findBy([
            'systemMessage' => $agent,
        ]);
        foreach ($calls as $call) {
            $this->em->remove($call);
        }
        $this->em->flush();

        // Delete the agent
        $this->em->remove($agent);
        $this->em->flush();

        // Renumber priorities for remaining agents with the same type and user
        // (bulk DQL update — decrement priority by 1 for all agents above the deleted one)
        $this->em->createQueryBuilder()
            ->update(AssistantRecurringMessage::class, 'a')
            ->set('a.priority', 'a.priority - 1')
            ->where('a.priority > :priority')
            ->andWhere('a.user = :user')
            ->andWhere('a.type = :type')
            ->setParameters([
                'priority' => $deletedPriority,
                'user' => $user,
                'type' => $agent->getType(),
            ])
            ->getQuery()
            ->execute();


        $this->flashSuccess($translator->translateFlash('deleted'));
        return $this->redirectToRoute('assistant_agent_list', [], Response::HTTP_SEE_OTHER);
    }
}
