<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Entity\MoneyNode;
use App\Repository\MoneyNodeRepository;

class MoneyService
{

    public function __construct(
        private AlexeyTranslator $translator,
        private MoneyNodeRepository $repository,
    ) {
    }

    public function getDataForChart(User $user): array
    {
        $chdata = [
            'labels' => [],
            'datasets' => [],
        ];

        $chdata = $this->prepareDataForChart(user: $user);
        return [
            'labels' => $chdata['labels'],
            'datasets' => $chdata['datasets'],
        ];
    }

    private function prepareDataForChart(User $user): array
    {
        $data = [];
        $labels = [];
        $datasets = [];
        $datasets['money_amount'] = [
            'label' => $this->translator->translateString(
                translationId: 'balance',
                module: 'money',
            ),
            'lineTension' => 0.3,
            'backgroundColor' => 'rgba(78, 115, 223, 0.05)',
            'borderColor' => 'rgba(78, 115, 223, 1)',
            'pointRadius' => 3,
            'pointBackgroundColor' => 'rgba(78, 115, 223, 1)',
            'pointBorderColor' => 'rgba(78, 115, 223, 1)',
            'pointHoverRadius' => 3,
            'pointHoverBackgroundColor' => 'rgba(78, 115, 223, 1)',
            'pointHoverBorderColor' => 'rgba(78, 115, 223, 1)',
            'pointHitRadius' => 10,
            'pointBorderWidth' => 2,
            'data' => [],
        ];

        $amounts = $this->getHistoricalChanges(user: $user);

        foreach ($amounts as $amount) {
            $labels[] = $amount['date'];
            $datasets['money_amount']['data'][] = $amount['amount'];
        }
        $data['labels'] = $labels;
        $data['datasets'] = $datasets;
        return $data;
    }

    private function getHistoricalChanges(User $user): array
    {
        $weeksToShow = 20;
        $result = [];
        $amountDate = new \DateTime('today');
        $window = new \DateInterval('P7D');
        $allNodes = $this->repository->getAllUserNodes(user: $user);
        for ($i = 0; $i < $weeksToShow; $i++) {
            $amount = 0.0;
            /**
             * @var MoneyNode
             */
            foreach ($allNodes as $node) {
                if (false === $node->isEdgeType()) {
                    $amount += $node->getBalance(onDate: $amountDate);
                }
            }
            $result[] = [
                'date' => $amountDate->format('d.m.Y'),
                'amount' => round(num: $amount, precision: 2),
            ];
            $amountDate = $amountDate->sub($window);
        }
        $result = array_reverse(array: $result);

        return $result;
    }
}