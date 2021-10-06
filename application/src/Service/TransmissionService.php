<?php

declare(strict_types=1);

namespace App\Service;

use Transmission\Transmission;
use App\Entity\NetworkStatistic;
use App\Class\TransmissionSettings;
use App\Service\SimpleSettingsService;
use Symfony\Contracts\Translation\TranslatorInterface;

// TODO: REFACTORISATION!!!
class TransmissionService
{
    private TransmissionSettings $settings;

    public function __construct(
        private NetworkUsageService $networkUsageService,
        private TranslatorInterface $translator,
        private SimpleSettingsService $simpleSettingsService,
    ) {
        $this->settings = new TransmissionSettings();
        $this->settings->selfConfigure($simpleSettingsService);
    }

    public function getSimulationChartData(): array
    {
        $chartData = [
            'speed' => [
                'labels' => [],
                'datasets' => [],
            ],
            'time' => [
                'labels' => [],
                'datasets' => [],
            ],
        ];
        $datasetStub = [
            'label' => $this->translator->trans('app.network.network_usage.throttling') . ' (kB/s)',
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
        $chartData['speed']['datasets'][0] = $datasetStub;
        $chartData['time']['datasets'][0] = $datasetStub;


        $inputSpeed = 1;
        $throttled = 0;
        while ($throttled < 1.5 * intval($this->settings->getTargetSpeed())) {
            $throttled = $this->settings->getProposedThrottleSpeed($inputSpeed * 1024);
            if ($throttled > TransmissionSettings::BOTTOM_SPEED) {
                $chartData['speed']['datasets'][0]['data'][] = $throttled;
                $chartData['speed']['labels'][] = $inputSpeed . ' kB/s';
            }
            $inputSpeed += 0.5;
        }


        $stat = $this->networkUsageService->getLatestStatistic();
        $throttled = $stat ? $this->settings->getProposedThrottleSpeed($stat->getTransferRateLeft()) : 0;
        $mockedProbingTime = new \DateTime('now');
        $window = new \DateInterval('PT1H');
        $maxChartPoints = 300;
        $chartPoints = 0;
        if ($throttled < intval($this->settings->getTargetSpeed())) {
            while ($throttled < intval($this->settings->getTargetSpeed())) {
                $throttled = $this->settings->getProposedThrottleSpeed($stat->getTransferRateLeft());
                $stat->setProbingTime($mockedProbingTime);
                $stat->setDataUploadedInFrame($stat->getDataUploadedInFrame() + $throttled * 1024 * 3600);
                $mockedProbingTime->add($window);
                $chartData['time']['datasets'][0]['data'][] = $throttled;
                $chartData['time']['labels'][] = $mockedProbingTime->format('d.m H:i');
                $chartPoints++;
                if ($chartPoints >= $maxChartPoints) {
                    break;
                }
            }
        } else {
            while ($throttled > intval($this->settings->getTargetSpeed())) {
                $throttled = $this->settings->getProposedThrottleSpeed($stat->getTransferRateLeft());
                $stat->setProbingTime($mockedProbingTime);
                $stat->setDataUploadedInFrame($stat->getDataUploadedInFrame() + $throttled * 1024 * 3600);
                $mockedProbingTime->add($window);
                $chartData['time']['datasets'][0]['data'][] = $throttled;
                $chartData['time']['labels'][] = $mockedProbingTime->format('d.m H:i');
                $chartPoints++;
                if ($chartPoints >= $maxChartPoints) {
                    break;
                }
            }
        }

        return $chartData;
    }

    public function adjustSpeed(): void
    {
        if ($this->settings->getIsActive() === SimpleSettingsService::UNIVERSAL_TRUTH) {
            $stat = $this->networkUsageService->getLatestStatistic();
            if ($stat instanceof NetworkStatistic) {
                $transmission = new Transmission($this->settings->getHost());
                $client = $transmission->getclient();
                $client->authenticate($this->settings->getUser(), $this->settings->getPassword());
                $session = $transmission->getSession();
                $proposedSpeed = $this->settings->getProposedThrottleSpeed($stat->getTransferRateLeft());
                $session->setDownloadSpeedLimit($proposedSpeed);
                $session->setAltSpeedDown($proposedSpeed);
                $session->save();
                if (SimpleSettingsService::UNIVERSAL_FALSE !== $this->settings->getAggressionAdapt()) {
                    $targetSpeed = intval($this->settings->getTargetSpeed());
                    $aggression = intval($this->settings->getAlgorithmAggression());
                    if ($proposedSpeed > $targetSpeed / 2) {
                        $aggression += 1;
                        if (
                            ($aggression > TransmissionSettings::MAX_AGGRESSION) &&
                            ($proposedSpeed > $targetSpeed) &&
                            (SimpleSettingsService::UNIVERSAL_TRUTH === $this->settings->getAllowSpeedBump())
                        ) {
                            $increasedTargetSpeed = $targetSpeed + 1;
                            if ($increasedTargetSpeed < (TransmissionSettings::TOP_SPEED / 2)) {
                                $this->settings->setTargetSpeed(strval($increasedTargetSpeed));
                            }
                        }
                    } elseif (
                        ($proposedSpeed < ($targetSpeed / 4)) &&
                        (TransmissionSettings::ADAPT_TYPE_UP_ONLY !== $this->settings->getAggressionAdapt())
                    ) {
                        $aggression -= 1;
                    }
                    $this->settings->setAlgorithmAggression(strval($aggression));
                }
                $this->settings->selfPersist($this->simpleSettingsService);
            }
        }
    }
}
