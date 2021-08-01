<?php

namespace App\Controller;

use App\Entity\NetworkMachine;
use App\Form\NetworkMachineType;
use App\Repository\NetworkMachineRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/network-machine')]
class NetworkMachineController extends AbstractController
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
        if ($this->isCsrfTokenValid('delete'.$networkMachine->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($networkMachine);
            $entityManager->flush();
        }

        return $this->redirectToRoute('network_machine_index', [], Response::HTTP_SEE_OTHER);
    }
}
