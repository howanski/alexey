<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\MoneyTransfer;
use App\Form\MoneyTransferType;
use App\Service\AlexeyTranslator;
use App\Form\MoneyTransferSplitType;
use App\Repository\MoneyTransferRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/money/transfer')]
class MoneyTransferController extends AbstractController
{
    #[Route('/', name: 'money_transfer_index', methods: ['GET'])]
    public function index(MoneyTransferRepository $moneyTransferRepository): Response
    {
        return $this->render('money_transfer/index.html.twig', [
            'money_transfers' => $moneyTransferRepository->getAllUserTransfers($this->getUser()),
        ]);
    }

    #[Route('/new', name: 'money_transfer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AlexeyTranslator $translator): Response
    {
        $moneyTransfer = new MoneyTransfer($this->getUser());
        $form = $this->createForm(MoneyTransferType::class, $moneyTransfer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($moneyTransfer);
            $entityManager->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));
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
        // TODO: Security
        return $this->render('money_transfer/show.html.twig', [
            'money_transfer' => $moneyTransfer,
        ]);
    }

    #[Route('/{id}/edit', name: 'money_transfer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MoneyTransfer $moneyTransfer, AlexeyTranslator $translator): Response
    {
        // TODO: Security
        $form = $this->createForm(MoneyTransferType::class, $moneyTransfer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));
            return $this->redirectToRoute('money_transfer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('money_transfer/edit.html.twig', [
            'money_transfer' => $moneyTransfer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'money_transfer_delete', methods: ['POST'])]
    public function delete(Request $request, MoneyTransfer $moneyTransfer, AlexeyTranslator $translator): Response
    {
        // TODO: Security
        if ($this->isCsrfTokenValid('delete' . $moneyTransfer->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($moneyTransfer);
            $entityManager->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('deleted'));
            return $this->redirectToRoute('money_transfer_index', [], Response::HTTP_SEE_OTHER);
        }
        $this->addFlash(type: 'nord11', message: $translator->translateFlash('delete_forbidden') . ' (Beta)');
        return $this->redirectToRoute('money_transfer_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/split', name: 'money_transfer_split', methods: ['GET', 'POST'])]
    public function split(MoneyTransfer $moneyTransfer, Request $request, AlexeyTranslator $translator): Response
    {
        // TODO: Security

        $initialData = [
            'targetNodePrimary' => $moneyTransfer->getTargetNode(),
            'targetNodeSecondary' => $moneyTransfer->getTargetNode(),
            'amountPrimary' => $moneyTransfer->getAmount(),
        ];

        $form = $this->createForm(
            type: MoneyTransferSplitType::class,
            data: $initialData,
            options: ['source' => $moneyTransfer],
        );
        $form->handleRequest(request: $request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var EntityManager $em
             */
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try { //TODO: move to service
                $user = $moneyTransfer->getUser();
                $sumOfAmounts = $moneyTransfer->getAmount();
                $sourceNode = $moneyTransfer->getSourceNode();
                $operationDate = $moneyTransfer->getOperationDate();
                $exchangeRate = $moneyTransfer->getExchangeRate();
                $comment = $moneyTransfer->getComment();
                $comment .= ' (' . $translator->translateString('split', 'money') . ')';

                $formData = $form->getData();

                $primaryTargetNode = $formData['targetNodePrimary'];
                $primaryAmount = $formData['amountPrimary'];

                $secondaryTargetNode = $formData['targetNodeSecondary'];
                $secondaryAmount = $sumOfAmounts - $primaryAmount;

                $newTransferPrimary = new MoneyTransfer($user);
                $newTransferPrimary->setAmount($primaryAmount);
                $newTransferPrimary->setSourceNode($sourceNode);
                $newTransferPrimary->setTargetNode($primaryTargetNode);
                $newTransferPrimary->setOperationDate($operationDate);
                $newTransferPrimary->setExchangeRate($exchangeRate);
                $newTransferPrimary->setComment($comment);
                $em->persist($newTransferPrimary);

                $newTransferSecondary = new MoneyTransfer($user);
                $newTransferSecondary->setAmount($secondaryAmount);
                $newTransferSecondary->setSourceNode($sourceNode);
                $newTransferSecondary->setTargetNode($secondaryTargetNode);
                $newTransferSecondary->setOperationDate($operationDate);
                $newTransferSecondary->setExchangeRate($exchangeRate);
                $newTransferSecondary->setComment($comment);
                $em->persist($newTransferSecondary);

                $em->remove($moneyTransfer);

                $em->flush();
                $em->getConnection()->commit();
                $this->addFlash(
                    type: 'nord14',
                    message: $translator->translateFlash(
                        translationId: 'split',
                        module: 'money',
                    )
                );
                return $this->redirectToRoute('money_transfer_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $em->getConnection()->rollBack();
                $this->addFlash(
                    type: 'nord11',
                    message: $e->getMessage(),
                );
            }
        }

        return $this->renderForm('money_transfer/split.html.twig', [
            'money_transfer' => $moneyTransfer,
            'form' => $form,
        ]);
    }
}
