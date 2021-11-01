<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\MoneyTransfer;
use App\Form\MoneyTransferType;
use App\Repository\MoneyTransferRepository;
use App\Service\AlexeyTranslator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/money/transfer')]
class MoneyTransferController extends AbstractController
{
    #[Route('/', name: 'money_transfer_index', methods: ['GET'])]
    public function index(MoneyTransferRepository $moneyTransferRepository): Response
    {
        return $this->render('money_transfer/index.html.twig', [
            'money_transfers' => $moneyTransferRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'money_transfer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AlexeyTranslator $translator): Response
    {
        $moneyTransfer = new MoneyTransfer();
        $form = $this->createForm(MoneyTransferType::class, $moneyTransfer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($moneyTransfer);
            $entityManager->flush();
            $this->addFlash(type: 'success', message: $translator->translateFlash('saved'));
            return $this->redirectToRoute('money_transfer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('money_transfer/new.html.twig', [
            'money_transfer' => $moneyTransfer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'money_transfer_show', methods: ['GET'])]
    public function show(MoneyTransfer $moneyTransfer): Response
    {
        return $this->render('money_transfer/show.html.twig', [
            'money_transfer' => $moneyTransfer,
        ]);
    }

    #[Route('/{id}/edit', name: 'money_transfer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MoneyTransfer $moneyTransfer, AlexeyTranslator $translator): Response
    {
        $form = $this->createForm(MoneyTransferType::class, $moneyTransfer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(type: 'success', message: $translator->translateFlash('saved'));
            return $this->redirectToRoute('money_transfer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('money_transfer/edit.html.twig', [
            'money_transfer' => $moneyTransfer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'money_transfer_delete', methods: ['POST'])]
    public function delete(Request $request, MoneyTransfer $moneyTransfer, AlexeyTranslator $translator): Response
    {
        if ($this->isCsrfTokenValid('delete' . $moneyTransfer->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($moneyTransfer);
            $entityManager->flush();
            $this->addFlash(type: 'success', message: $translator->translateFlash('deleted'));
        }

        return $this->redirectToRoute('money_transfer_index', [], Response::HTTP_SEE_OTHER);
    }
}
