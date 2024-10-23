<?php

declare(strict_types=1);

namespace App\Controller;

use App\Class\DynamicCard;
use App\Entity\NetworkMachine;
use App\Form\NetworkMachineType;
use App\Message\AsyncJob;
use App\Repository\NetworkMachineRepository;
use App\Service\AlexeyTranslator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/network/machines')]
final class NetworkMachineController extends AlexeyAbstractController
{
    #[Route('/', name: 'network_machine_index', methods: ['GET'])]
    public function index(NetworkMachineRepository $networkMachineRepository): Response
    {
        return $this->render('network_machine/index.html.twig', [
            'network_machines' => $networkMachineRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'network_machine_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AlexeyTranslator $translator): Response
    {
        $networkMachine = new NetworkMachine();
        $form = $this->createForm(NetworkMachineType::class, $networkMachine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($networkMachine);
            $this->em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));
            return $this->redirectToRoute('network_machine_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('network_machine/new.html.twig', [
            'network_machine' => $networkMachine,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'network_machine_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $networkMachine = $this->fetchEntityById(className: NetworkMachine::class, id: $id);
        return $this->render('network_machine/show.html.twig', [
            'network_machine' => $networkMachine,
        ]);
    }

    #[Route('/{id}/edit', name: 'network_machine_edit', methods: ['GET', 'POST'])]
    public function edit(
        AlexeyTranslator $translator,
        int $id,
        Request $request,
    ): Response {
        $networkMachine = $this->fetchEntityById(className: NetworkMachine::class, id: $id);
        $form = $this->createForm(NetworkMachineType::class, $networkMachine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));
            return $this->redirectToRoute('network_machine_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('network_machine/edit.html.twig', [
            'network_machine' => $networkMachine,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'network_machine_delete', methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
    ): Response {
        $networkMachine = $this->fetchEntityById(className: NetworkMachine::class, id: $id);
        if ($this->isCsrfTokenValid('delete' . $networkMachine->getId(), $request->request->get('_token'))) {
            $this->em->remove($networkMachine);
            $this->em->flush();
        }

        return $this->redirectToRoute('network_machine_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/wake/and-back-to/{backRoute}', name: 'network_machine_wake', methods: ['GET'])]
    public function wake(
        AlexeyTranslator $translator,
        int $id,
        MessageBusInterface $bus,
        string $backRoute,
    ): Response {
        $networkMachine = $this->fetchEntityById(className: NetworkMachine::class, id: $id);
        $payload = [
            'wakeDestination' => $networkMachine->getWakeDestination(),
            'macAddress' => $networkMachine->getMacAddress(),
        ];
        $message = new AsyncJob(
            jobType: AsyncJob::TYPE_WAKE_ON_LAN,
            payload: $payload,
        );
        $bus->dispatch($message);
        $this->addFlash(type: 'nord14', message: $translator->translateFlash('signal_dispatched'));
        return $this->redirectToRoute($backRoute, [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/card-data', name: 'network_machine_dynacard', methods: ['GET'])]
    public function dynacard(
        int $id,
        Request $request,
    ): Response {
        if ($request->isXmlHttpRequest()) {
            $networkMachine = $this->fetchEntityById(className: NetworkMachine::class, id: $id);
            $render = $this->renderView(
                view: 'network_machine/card_content.html.twig',
                parameters: [
                    'network_machine' => $networkMachine,
                ],
            );

            $card = new DynamicCard();
            $card->setRawContent($render);
            return $card->toResponse();
        } else {
            return $this->redirectToRoute(route: 'dashboard');
        }
    }
}
