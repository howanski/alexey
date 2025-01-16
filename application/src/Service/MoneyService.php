<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\MoneyNode;
use App\Entity\MoneyTransfer;
use App\Entity\User;
use App\Model\MoneyNodeSettings;
use App\Repository\MoneyNodeRepository;
use App\Repository\MoneyTransferRepository;
use DateTime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class MoneyService
{
    public function __construct(
        private AlexeyTranslator $translator,
        private MoneyNodeRepository $moneyNodeRepository,
        private MoneyTransferRepository $moneyTransferRepository,
        private SimpleSettingsService $simpleSettingsService,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getMoneyNodeChoicesForForm(User $user, array $includeNodes = []): array
    {
        $choices = [];
        $settings = new MoneyNodeSettings($user);
        $settings->selfConfigure($this->simpleSettingsService);
        $allNodes = $this->moneyNodeRepository->getSelectableUserNodes(user: $user, groupId: null);
        foreach ($includeNodes as $includeNode) {
            if ($includeNode && !in_array(needle: $includeNode, haystack: $allNodes)) {
                $allNodes[] = $includeNode;
            }
        }

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

    public function getMoneyTransferMonthSelectorPills(User $user, string $selectedMonth): array
    {
        $randomString = 'XYZZY';
        $pillsLimit = 6;
        $pills = [];
        $basePath = $this->urlGenerator->generate(
            name: 'money_transfer_index',
            parameters: [
                'month' => $randomString,
            ]
        );
        $usableMonths = $this->moneyTransferRepository->getUserTransferMonths(user: $user);
        foreach ($usableMonths as $month) {
            $isCurrent = $month === $selectedMonth;
            $pills[] = [
                'name' => $month,
                'path' => str_replace(search: $randomString, replace: $month, subject: $basePath),
                'active' => $isCurrent,
            ];
            if (true === $isCurrent) {
                while (count($pills) > ($pillsLimit / 2) + 1) {
                    array_shift($pills);
                }
            }
        }

        while (count($pills) > $pillsLimit + 1) {
            array_pop($pills);
        }

        return $pills;
    }

    public function getDataForChart(User $user): array
    {
        $chartData = [
            'labels' => [],
            'datasets' => [],
        ];

        $chartData = $this->prepareDataForChart(user: $user);
        return [
            'labels' => $chartData['labels'],
            'datasets' => $chartData['datasets'],
        ];
    }

    public function getDataForForecastChart(User $user): array
    {
        $chartData = [
            'labels' => [],
            'datasets' => [],
        ];

        $chartData = $this->prepareDataForForecastChart(user: $user);
        return [
            'labels' => $chartData['labels'],
            'datasets' => $chartData['datasets'],
        ];
    }

    public function getDataForEdgePieChart(string $chartType, DateTime $month, User $user): array
    {
        $settings = new MoneyNodeSettings($user);
        $settings->selfConfigure($this->simpleSettingsService);
        $totalIncome = 0;
        $totalOutcome = 0;
        $transfersConsidered = $this->moneyTransferRepository->getAllUserTransfersFromMonth(
            user: $user,
            fromMonth: $month,
        );
        $labels = [];
        $data = [];
        $colors = [];
        /** @var MoneyTransfer $transfer */
        foreach ($transfersConsidered as $transfer) {
            $source = $transfer->getSourceNode();
            $target = $transfer->getTargetNode();
            if ($source->isEdgeType()) {
                $totalIncome += $transfer->getAmount();
            }
            if ($target->isEdgeType()) {
                $totalOutcome += $transfer->getExchangedAmount();
            }
            if ($chartType === 'outcome_grouped') {
                if ($target->isEdgeType()) {
                    $id = $settings->getGroupName($target->getNodeGroup());
                    $prev = 0;
                    if (array_key_exists(key: $id, array: $data)) {
                        $prev = $data[$id];
                    }
                    $labels[$id] = $id;
                    $data[$id] = $transfer->getExchangedAmount() + $prev;
                    $colors[$id] = $this->mdColor($target->getName() . date('s'));
                }
            } elseif ($chartType === 'outcome') {
                if ($target->isEdgeType()) {
                    $id = $target->getId();
                    $prev = 0;
                    if (array_key_exists(key: $id, array: $data)) {
                        $prev = $data[$id];
                    }
                    $labels[$id] = $target->getName();
                    $data[$id] = $transfer->getExchangedAmount() + $prev;
                    $colors[$id] = $this->mdColor($target->getName() . date('s'));
                }
            } elseif ($chartType === 'income') {
                if ($source->isEdgeType()) {
                    $id = $source->getId();
                    $prev = 0;
                    if (array_key_exists(key: $id, array: $data)) {
                        $prev = $data[$id];
                    }
                    $labels[$id] = $source->getName();
                    $data[$id] = $transfer->getAmount() + $prev;
                    $colors[$id] = $this->mdColor($source->getName() . date('s'));
                }
            } else {
                throw new \Exception('Unknown chart type');
            }
        }
        $savings = $totalIncome - $totalOutcome;
        if ($savings > 0) {
            if (in_array(needle: $chartType, haystack: ['outcome', 'outcome_grouped'])) {
                $id = md5(strval($savings));
                $labels[$id] = $this->translator->translateString('remaining_amount', 'money');
                $data[$id] = $savings;
                $colors[$id] = $this->mdColor($id . date('s'));
            }
        }
        $this->stripArrayKeys($labels);
        $this->stripArrayKeys($data);
        $this->stripArrayKeys($colors);
        $data = [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => ['#2e3440']
                ]
            ]
        ];
        return $data;
    }

    private function mdColor(string $source): string
    {
        return '#' . substr(md5($source), 0, 6);
    }

    private function stripArrayKeys(array &$sourceArray): void
    {
        $keyless = [];
        ksort($sourceArray);
        foreach ($sourceArray as $val) {
            $keyless[] = $val;
        }
        $sourceArray = $keyless;
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
        $allNodes = $this->moneyNodeRepository->getAllUserNodes(user: $user, groupId: null);
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
        $weeksLookingBack = 25;
        $weeksInFuture = 25;
        $allNodes = $this->moneyNodeRepository->getAllUserNodes(user: $user, groupId: null);
        $amountDate = new \DateTime('today');
        $window = new \DateInterval('P7D');
        $bigWindow = new \DateInterval('P' . $weeksLookingBack . 'W');



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

        $weeklyGrowth = ($currentAmount - $historicalAmount) / $weeksLookingBack;

        for ($i = 0; $i < $weeksInFuture; $i++) {
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
