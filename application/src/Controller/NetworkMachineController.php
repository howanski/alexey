<?php

declare(strict_types=1);

namespace App\Controller;

use App\Class\DynamicCard;
use App\Entity\NetworkMachine;
use App\Form\NetworkMachineType;
use App\Message\AsyncJob;
use App\Repository\NetworkMachineRepository;
use App\Service\AlexeyTranslator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/network/machines')]
final class NetworkMachineController extends AbstractController
{
    #[Route('/', name: 'network_machine_index', methods: ['GET'])]
    public function index(NetworkMachineRepository $networkMachineRepository): Response
    {
        return $this->render('network_machine/index.html.twig', [
            'network_machines' => $networkMachineRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'network_machine_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $networkMachine = new NetworkMachine();
        $form = $this->createForm(NetworkMachineType::class, $networkMachine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($networkMachine);
            $entityManager->flush();

            return $this->redirectToRoute('network_machine_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('network_machine/new.html.twig', [
            'network_machine' => $networkMachine,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'network_machine_show', methods: ['GET'])]
    public function show(NetworkMachine $networkMachine): Response
    {
        return $this->render('network_machine/show.html.twig', [
            'network_machine' => $networkMachine,
        ]);
    }

    #[Route('/{id}/edit', name: 'network_machine_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, NetworkMachine $networkMachine): Response
    {
        $form = $this->createForm(NetworkMachineType::class, $networkMachine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('network_machine_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('network_machine/edit.html.twig', [
            'network_machine' => $networkMachine,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'network_machine_delete', methods: ['POST'])]
    public function delete(Request $request, NetworkMachine $networkMachine): Response
    {
        if ($this->isCsrfTokenValid('delete' . $networkMachine->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($networkMachine);
            $entityManager->flush();
        }

        return $this->redirectToRoute('network_machine_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/wake/and-back-to/{backRoute}', name: 'network_machine_wake', methods: ['GET'])]
    public function wake(
        NetworkMachine $networkMachine,
        string $backRoute,
        MessageBusInterface $bus,
        AlexeyTranslator $translator,
    ): Response {
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
    public function dynacard(NetworkMachine $networkMachine, AlexeyTranslator $t, Request $request): Response
    {
        $mod = 'network_machines';
        if (is_null($networkMachine->getLastSeen())) {
            $lastSeen = $t->translateString(translationId: 'never_seen', module: $mod);
        } else {
            $lastSeen = $t->translateString(translationId: 'last_seen', module: $mod) . ' ' .
                $networkMachine->getLastSeenReadable($request->getLocale());
        }

        $card = new DynamicCard(
            headerText: $t->translateString(translationId: 'machine', module: $mod),
            headerValue: $networkMachine->getName(),
            footerValue: $lastSeen,
        );
        return $card->toResponse();
    }
}
