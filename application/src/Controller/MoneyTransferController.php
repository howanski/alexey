<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\MoneyTransfer;
use App\Form\MoneyTransferSplitType;
use App\Form\MoneyTransferType;
use App\Repository\MoneyTransferRepository;
use App\Service\AlexeyTranslator;
use App\Service\MoneyService;
use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/money/transfer')]
final class MoneyTransferController extends AlexeyAbstractController
{
    #[Route('/', name: 'money_transfer_index', methods: ['GET'])]
    public function index(
        MoneyService $service,
        MoneyTransferRepository $moneyTransferRepository,
        Request $request,
    ): Response {
        $user = $this->alexeyUser();
        $filters = $request->query->all();
        if (array_key_exists(key: 'month', array: $filters)) {
            $monthStr = $filters['month'];
        } else {
            $monthStr = date('Y-m');
        }

        $pills = $service->getMoneyTransferMonthSelectorPills(
            user: $user,
            selectedMonth: $monthStr,
        );

        $month = new DateTime($monthStr);
        return $this->render('money_transfer/index.html.twig', [
            'month_selector_pills' => $pills,
            'money_transfers' => $moneyTransferRepository
                ->getAllUserTransfersFromMonth(
                    user: $user,
                    fromMonth: $month,
                ),
            'month' => $monthStr,
        ]);
    }

    #[Route('/new', name: 'money_transfer_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        AlexeyTranslator $translator,
        MoneyService $service,
    ): Response {
        $user = $this->alexeyUser();
        $moneyTransfer = new MoneyTransfer($user);
        $form = $this->createForm(
            type: MoneyTransferType::class,
            data: $moneyTransfer,
            options: [
                'money_node_choices' => $service->getMoneyNodeChoicesForForm($user),
                'locale' => $request->getLocale(),
            ],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($moneyTransfer);
            $this->em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));
            return $this->redirectToRoute('money_transfer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('money_transfer/new.html.twig', [
            'money_transfer' => $moneyTransfer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'money_transfer_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $moneyTransfer = $this->fetchEntityById(className: MoneyTransfer::class, id: $id);
        $user = $this->alexeyUser();
        if (false === ($user === $moneyTransfer->getUser())) {
            return $this->redirectToRoute('money_transfer_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('money_transfer/show.html.twig', [
            'money_transfer' => $moneyTransfer,
        ]);
    }

    #[Route('/{id}/edit', name: 'money_transfer_edit', methods: ['GET', 'POST'])]
    public function edit(
        AlexeyTranslator $translator,
        int $id,
        MoneyService $service,
        Request $request,
    ): Response {
        $user = $this->alexeyUser();
        $moneyTransfer = $this->fetchEntityById(className: MoneyTransfer::class, id: $id);
        if (false === ($user === $moneyTransfer->getUser())) {
            return $this->redirectToRoute('money_transfer_index', [], Response::HTTP_SEE_OTHER);
        }
        $form = $this->createForm(
            type: MoneyTransferType::class,
            data: $moneyTransfer,
            options: [
                'money_node_choices' => $service->getMoneyNodeChoicesForForm(
                    user: $user,
                    includeNodes: [
                        $moneyTransfer->getSourceNode(),
                        $moneyTransfer->getTargetNode(),
                    ],
                ),
                'locale' => $request->getLocale(),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));
            return $this->redirectToRoute('money_transfer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('money_transfer/edit.html.twig', [
            'money_transfer' => $moneyTransfer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'money_transfer_delete', methods: ['POST'])]
    public function delete(
        AlexeyTranslator $translator,
        int $id,
        Request $request,
    ): Response {
        $user = $this->alexeyUser();
        $moneyTransfer = $this->fetchEntityById(className: MoneyTransfer::class, id: $id);
        if (false === ($user === $moneyTransfer->getUser())) {
            return $this->redirectToRoute('money_transfer_index', [], Response::HTTP_SEE_OTHER);
        }
        if ($this->isCsrfTokenValid('delete' . $moneyTransfer->getId(), $request->request->get('_token'))) {
            $this->em->remove($moneyTransfer);
            $this->em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('deleted'));
            return $this->redirectToRoute('money_transfer_index', [], Response::HTTP_SEE_OTHER);
        }
        $this->addFlash(type: 'nord11', message: $translator->translateFlash('delete_forbidden') . ' (Beta)');
        return $this->redirectToRoute('money_transfer_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/split', name: 'money_transfer_split', methods: ['GET', 'POST'])]
    public function split(
        AlexeyTranslator $translator,
        int $id,
        MoneyService $service,
        Request $request,
    ): Response {
        $user = $this->alexeyUser();
        $moneyTransfer = $this->fetchEntityById(className: MoneyTransfer::class, id: $id);
        if (false === ($user === $moneyTransfer->getUser())) {
            return $this->redirectToRoute('money_transfer_index', [], Response::HTTP_SEE_OTHER);
        }
        $initialData = [
            'targetNodePrimary' => $moneyTransfer->getTargetNode(),
            'targetNodeSecondary' => $moneyTransfer->getTargetNode(),
            'amountPrimary' => $moneyTransfer->getAmount(),
        ];

        $form = $this->createForm(
            type: MoneyTransferSplitType::class,
            data: $initialData,
            options: [
                'source' => $moneyTransfer,
                'money_node_choices' => $service->getMoneyNodeChoicesForForm(
                    user: $user,
                    includeNodes: [
                        $moneyTransfer->getSourceNode(),
                        $moneyTransfer->getTargetNode(),
                    ],
                ),
            ],
        );
        $form->handleRequest(request: $request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->getConnection()->beginTransaction();
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
                $primaryAmount = round(intval($formData['amountPrimary'] * 100) / 100, 2);

                $secondaryTargetNode = $formData['targetNodeSecondary'];
                $secondaryAmount = round((intval($sumOfAmounts * 100) - intval($primaryAmount * 100)) / 100, 2);

                $newTransferPrimary = new MoneyTransfer($user);
                $newTransferPrimary->setAmount($primaryAmount);
                $newTransferPrimary->setSourceNode($sourceNode);
                $newTransferPrimary->setTargetNode($primaryTargetNode);
                $newTransferPrimary->setOperationDate($operationDate);
                $newTransferPrimary->setExchangeRate($exchangeRate);
                $newTransferPrimary->setComment($comment);
                $this->em->persist($newTransferPrimary);

                $newTransferSecondary = new MoneyTransfer($user);
                $newTransferSecondary->setAmount($secondaryAmount);
                $newTransferSecondary->setSourceNode($sourceNode);
                $newTransferSecondary->setTargetNode($secondaryTargetNode);
                $newTransferSecondary->setOperationDate($operationDate);
                $newTransferSecondary->setExchangeRate($exchangeRate);
                $newTransferSecondary->setComment($comment);
                $this->em->persist($newTransferSecondary);

                $this->em->remove($moneyTransfer);

                $this->em->flush();
                $this->em->getConnection()->commit();
                $this->addFlash(
                    type: 'nord14',
                    message: $translator->translateFlash(
                        translationId: 'split',
                        module: 'money',
                    )
                );
                return $this->redirectToRoute('money_transfer_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->em->getConnection()->rollBack();
                $this->addFlash(
                    type: 'nord11',
                    message: $e->getMessage(),
                );
            }
        }

        return $this->render('money_transfer/split.html.twig', [
            'money_transfer' => $moneyTransfer,
            'form' => $form,
        ]);
    }

    #[Route('/edge-transfers-chart-data/{type}/{month}', name: 'money_edge_transfers_chart_data', methods: ['GET'])]
    public function pieChartData(
        string $type,
        DateTime $month,
        Request $request,
        MoneyService $service,
    ): Response {
        if (false === $request->isXmlHttpRequest()) {
            return $this->redirectToRoute(route: 'dashboard');
        }

        $user = $this->alexeyUser();

        $data = $service->getDataForEdgePieChart(
            chartType: $type,
            month: $month,
            user: $user,
        );

        return new JsonResponse(data: $data);
    }
}
