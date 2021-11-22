<?php

declare(strict_types=1);

namespace App\Service;

use App\Class\MoneyNodeSettings;
use App\Entity\MoneyNode;
use App\Entity\User;
use App\Repository\MoneyNodeRepository;

final class MoneyService
{

    public function __construct(
        private AlexeyTranslator $translator,
        private MoneyNodeRepository $repository,
        private SimpleSettingsService $simpleSettingsService,
    ) {
    }

    public function getMoneyNodeChoicesForForm(User $user)
    {
        $choices = [];
        $settings = new MoneyNodeSettings($user);
        $settings->selfConfigure($this->simpleSettingsService);
        $allNodes = $this->repository->getAllUserNodes(user: $user, groupId: null);
        /** @var MoneyNode $node */
        foreach ($allNodes as $node) {
            $groupName = $settings->getGroupName(
                groupId: $node->getNodeGroup(),
            );
            $choices[$groupName][] = $node;
        }
        ksort($choices);
        return $choices;
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

    public function getDataForForecastChart(User $user): array
    {
        $chdata = [
            'labels' => [],
            'datasets' => [],
        ];

        $chdata = $this->prepareDataForForecastChart(user: $user);
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
        $allNodes = $this->repository->getAllUserNodes(user: $user, groupId: null);
        for ($i = 0; $i < $weeksToShow; $i++) {
            $amount = 0.0;
            /** @var MoneyNode $node */
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

    private function prepareDataForForecastChart(User $user): array
    {
        $data = [];
        $labels = [];
        $datasets = [];
        $datasets['money_amount'] = [
            'label' => $this->translator->translateString(
                translationId: 'forecast',
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

        $amounts = $this->getForecastChanges(user: $user);

        foreach ($amounts as $amount) {
            $labels[] = $amount['date'];
            $datasets['money_amount']['data'][] = $amount['amount'];
        }
        $data['labels'] = $labels;
        $data['datasets'] = $datasets;
        return $data;
    }

    private function getForecastChanges(User $user): array
    {
        $result = [];
        $allNodes = $this->repository->getAllUserNodes(user: $user, groupId: null);
        $amountDate = new \DateTime('today');
        $window = new \DateInterval('P7D');
        $bigWindow = new \DateInterval('P' . intval(7 * 20) . 'D');



        $currentAmount = 0.0;
        /** @var MoneyNode $node */
        foreach ($allNodes as $node) {
            if (false === $node->isEdgeType()) {
                $currentAmount += $node->getBalance(onDate: $amountDate);
            }
        }

        $estimationStart = (clone $amountDate)->sub($bigWindow);

        $historicalAmount = 0.0;
        /** @var MoneyNode $node */
        foreach ($allNodes as $node) {
            if (false === $node->isEdgeType()) {
                $historicalAmount += $node->getBalance(onDate: $estimationStart);
            }
        }

        $weeklyGrowth = round(($currentAmount - $historicalAmount) / 20, 2);

        for ($i = 0; $i < 50; $i++) {
            $result[] = [
                'date' => $amountDate->format('d.m'),
                'amount' => round(num: $currentAmount, precision: 2),
            ];
            $currentAmount += $weeklyGrowth;
            $amountDate = $amountDate->add($window);
        }

        return $result;
    }
}
