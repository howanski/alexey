<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\MoneyNode;
use App\Form\MoneyNodeType;
use App\Repository\MoneyNodeRepository;
use App\Service\AlexeyTranslator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/money/node')]
class MoneyNodeController extends AbstractController
{
    #[Route('/', name: 'money_node_index', methods: ['GET'])]
    public function index(MoneyNodeRepository $moneyNodeRepository): Response
    {
        return $this->render('money_node/index.html.twig', [
            'money_nodes' => $moneyNodeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'money_node_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AlexeyTranslator $translator): Response
    {
        $moneyNode = new MoneyNode($this->getUser());
        $form = $this->createForm(MoneyNodeType::class, $moneyNode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($moneyNode);
            $entityManager->flush();
            $this->addFlash(type: 'success', message: $translator->translateFlash('saved'));
            return $this->redirectToRoute('money_node_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('money_node/new.html.twig', [
            'money_node' => $moneyNode,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'money_node_show', methods: ['GET'])]
    public function show(MoneyNode $moneyNode): Response
    {
        return $this->render('money_node/show.html.twig', [
            'money_node' => $moneyNode,
        ]);
    }

    #[Route('/{id}/edit', name: 'money_node_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MoneyNode $moneyNode, AlexeyTranslator $translator): Response
    {
        $form = $this->createForm(MoneyNodeType::class, $moneyNode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(type: 'success', message: $translator->translateFlash('saved'));
            return $this->redirectToRoute('money_node_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('money_node/edit.html.twig', [
            'money_node' => $moneyNode,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'money_node_delete', methods: ['POST'])]
    public function delete(Request $request, MoneyNode $moneyNode, AlexeyTranslator $translator): Response
    {
        if ($this->isCsrfTokenValid('delete' . $moneyNode->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($moneyNode);
            $entityManager->flush();
            $this->addFlash(type: 'success', message: $translator->translateFlash('deleted'));
        }

        return $this->redirectToRoute('money_node_index', [], Response::HTTP_SEE_OTHER);
    }
}
