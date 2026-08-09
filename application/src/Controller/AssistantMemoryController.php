<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AssistantMemory;
use App\Entity\User;
use App\Form\AssistantMemoryType;
use App\Repository\AssistantMemoryRepository;
use App\Service\AlexeyTranslator;
use App\Service\AssistantMemoryService;
use App\Service\AssistantService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/assistant/memory')]
final class AssistantMemoryController extends AlexeyAbstractController
{
    #[Route('/list', name: 'assistant_memory_list', methods: ['GET'])]
    public function list(
        AssistantMemoryRepository $memoryRepository,
    ): Response {
        $user = $this->alexeyUser();
        if (!($user instanceof User)) {
            return $this->banishToLoginPage();
        }

        $memories = $memoryRepository->getAllMemories($user);

        return $this->render('assistant/memory_list.html.twig', [
            'memories' => $memories,
        ]);
    }

    #[Route('/confirm/{id}', name: 'assistant_memory_approve', methods: ['POST'])]
    public function confirm(
        int $id,
        AlexeyTranslator $translator,
        AssistantMemoryService $memoryService,
        Request $request,
    ): Response {
        $user = $this->alexeyUser();
        if (!($user instanceof User)) {
            return $this->banishToLoginPage();
        }

        if (!$this->isCsrfTokenValid('approve_memory_' . $id, (string) $request->request->get('_token'))) {
            $this->flashError($translator->translateFlash('delete_forbidden'));
            return $this->redirectToRoute('assistant_memory_list');
        }

        $memory = $this->fetchEntityById(AssistantMemory::class, $id);
        if (!($memory instanceof AssistantMemory)) {
            $this->flashError($translator->translateFlash('delete_forbidden'));
            return $this->redirectToRoute('assistant_memory_list');
        }
        if (!($memory->getAssistant()->getUser() === $user)) {
            $this->flashError($translator->translateFlash('delete_forbidden'));
            return $this->redirectToRoute('assistant_memory_list');
        }

        $memoryService->confirmMemory($user, $id);
        $this->flashSuccess($translator->translateFlash('saved'));
        return $this->redirectToRoute('assistant_memory_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/reject/{id}', name: 'assistant_memory_reject', methods: ['POST'])]
    public function reject(
        int $id,
        AlexeyTranslator $translator,
        AssistantMemoryService $memoryService,
        Request $request,
    ): Response {
        $user = $this->alexeyUser();
        if (!($user instanceof User)) {
            return $this->banishToLoginPage();
        }

        if (!$this->isCsrfTokenValid('reject_memory_' . $id, (string) $request->request->get('_token'))) {
            $this->flashError($translator->translateFlash('delete_forbidden'));
            return $this->redirectToRoute('assistant_memory_list');
        }

        $memory = $this->fetchEntityById(AssistantMemory::class, $id);
        if (!($memory instanceof AssistantMemory)) {
            $this->flashError($translator->translateFlash('delete_forbidden'));
            return $this->redirectToRoute('assistant_memory_list');
        }
        if (!($memory->getAssistant()->getUser() === $user)) {
            $this->flashError($translator->translateFlash('delete_forbidden'));
            return $this->redirectToRoute('assistant_memory_list');
        }

        $memoryService->rejectMemory($user, $id);
        $this->flashSuccess($translator->translateFlash('deleted'));
        return $this->redirectToRoute('assistant_memory_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/new', name: 'assistant_memory_new', methods: ['GET', 'POST'])]
    public function new(
        AlexeyTranslator $translator,
        AssistantService $service,
        Request $request,
    ): Response {
        $user = $this->alexeyUser();
        if (!($user instanceof User)) {
            return $this->banishToLoginPage();
        }

        $memory = new AssistantMemory();
        // User-created memories start as CONFIRMED immediately
        $memory->setStatus(AssistantMemory::STATUS_RESOLVED_BY_USER);

        $form = $this->createForm(AssistantMemoryType::class, $memory, [
            'model_choices' => $service->getModelChoices($user, false)
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $memory->acceptUserVersion();
            $this->em->persist($memory);
            $this->em->flush();
            $this->flashSuccess($translator->translateFlash('saved'));
            return $this->redirectToRoute('assistant_memory_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('assistant/memory_new.html.twig', [
            'memory' => $memory,
            'form' => $form,
        ]);
    }

    #[Route('/edit/{id}', name: 'assistant_memory_edit', methods: ['GET', 'POST'])]
    public function edit(
        AlexeyTranslator $translator,
        AssistantService $service,
        int $id,
        Request $request,
    ): Response {
        $user = $this->alexeyUser();
        if (!($user instanceof User)) {
            return $this->banishToLoginPage();
        }

        $memory = $this->fetchEntityById(AssistantMemory::class, $id);
        if (!($memory instanceof AssistantMemory)) {
            return $this->redirectToRoute('assistant_memory_list', [], Response::HTTP_SEE_OTHER);
        }
        if (!($memory->getAssistant()->getUser() === $user)) {
            return $this->redirectToRoute('assistant_memory_list', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(AssistantMemoryType::class, $memory, [
            'model_choices' => $service->getModelChoices($user, false),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $memory->acceptUserVersion();
            $this->em->persist($memory);
            $this->em->flush();
            $this->flashSuccess($translator->translateFlash('saved'));
            return $this->redirectToRoute('assistant_memory_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('assistant/memory_edit.html.twig', [
            'memory' => $memory,
            'form' => $form,
            'breadcrumb_label' => 'edit',
        ]);
    }

    #[Route('/compare-versions/{id}', name: 'assistant_memory_compare', methods: ['GET'])]
    public function compareVersions(
        int $id,
    ): Response {
        $user = $this->alexeyUser();
        if (!($user instanceof User)) {
            return $this->banishToLoginPage();
        }

        $memory = $this->fetchEntityById(AssistantMemory::class, $id);
        if (!($memory instanceof AssistantMemory)) {
            return $this->redirectToRoute('assistant_memory_list', [], Response::HTTP_SEE_OTHER);
        }
        if (!($memory->getAssistant()->getUser() === $user)) {
            return $this->redirectToRoute('assistant_memory_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('assistant/memory_compare.html.twig', [
            'memory' => $memory,
        ]);
    }

    #[Route('/delete/{id}', name: 'assistant_memory_delete', methods: ['POST'])]
    public function delete(
        int $id,
        AlexeyTranslator $translator,
        Request $request,
    ): Response {
        $user = $this->alexeyUser();
        if (!($user instanceof User)) {
            return $this->banishToLoginPage();
        }

        if (!$this->isCsrfTokenValid('delete_memory_' . $id, (string) $request->request->get('_token'))) {
            $this->flashError($translator->translateFlash('delete_forbidden'));
            return $this->redirectToRoute('assistant_memory_list');
        }

        $memory = $this->fetchEntityById(AssistantMemory::class, $id);
        if (!($memory instanceof AssistantMemory)) {
            $this->flashError($translator->translateFlash('delete_forbidden'));
            return $this->redirectToRoute('assistant_memory_list');
        }
        if (!($memory->getAssistant()->getUser() === $user)) {
            $this->flashError($translator->translateFlash('delete_forbidden'));
            return $this->redirectToRoute('assistant_memory_list');
        }

        $this->em->remove($memory);
        $this->em->flush();
        $this->flashSuccess($translator->translateFlash('deleted'));
        return $this->redirectToRoute('assistant_memory_list', [], Response::HTTP_SEE_OTHER);
    }
}
