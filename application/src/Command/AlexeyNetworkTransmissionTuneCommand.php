<?php

declare(strict_types=1);

namespace App\Command;

use App\Class\TransmissionSettings;
use App\Entity\NetworkStatistic;
use App\Service\NetworkUsageService;
use App\Service\SimpleSettingsService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Transmission\Transmission;

#[AsCommand(
    name: 'alexey:network:transmission:tune',
    description: 'Add a short description for your command',
)]
class AlexeyNetworkTransmissionTuneCommand extends Command
{
    private TransmissionSettings $settings;

    public function __construct(
        private NetworkUsageService $networkUsageService,
        private SimpleSettingsService $simpleSettingsService
    ) {
        parent::__construct();
        $this->settings = new TransmissionSettings();
        $this->settings->selfConfigure($this->simpleSettingsService);
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'sleepSecondsAfterFinish',
                InputArgument::REQUIRED,
                'Sleep amount after job finish - useful when running from supervisord instead of CRON'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $sleepSecondsAfterFinish = intval($input->getArgument('sleepSecondsAfterFinish'));
        $io->note('Starting!');
        if ($this->settings->getIsActive() == SimpleSettingsService::UNIVERSAL_TRUTH) {
            $stat = $this->networkUsageService->getLatestStatistic();
            if ($stat instanceof NetworkStatistic) {
                $transmission = new Transmission($this->settings->getHost());
                $client = $transmission->getclient();
                $client->authenticate($this->settings->getUser(), $this->settings->getPassword());
                $session = $transmission->getSession();
                $proposedSpeed = $this->settings->getProposedThrottleSpeed($stat->getTransferRateLeft());
                $io->note('Setting ' . $proposedSpeed . 'kBps');
                $session->setDownloadSpeedLimit($proposedSpeed);
                $session->setAltSpeedDown($proposedSpeed);
                $session->save();
                if (SimpleSettingsService::UNIVERSAL_FALSE != $this->settings->getAggressionAdapt()) {
                    $targetSpeed = intval($this->settings->getTargetSpeed());
                    $aggression = intval($this->settings->getAlgorithmAggression());
                    if ($proposedSpeed > $targetSpeed / 2) {
                        $aggression += 1;
                        if (
                            ($aggression > TransmissionSettings::MAX_AGGRESSION) &&
                            ($proposedSpeed > $targetSpeed) &&
                            (TransmissionSettings::ADAPT_TYPE_UP_ONLY == $this->settings->getAggressionAdapt())
                        ) {
                            $increasedTargetSpeed = $targetSpeed + 1;
                            if ($increasedTargetSpeed < (TransmissionSettings::TOP_SPEED / 2)) {
                                $this->settings->setTargetSpeed(strval($increasedTargetSpeed));
                            }
                        }
                    } elseif (
                        ($proposedSpeed < ($targetSpeed / 4)) &&
                        (TransmissionSettings::ADAPT_TYPE_UP_ONLY != $this->settings->getAggressionAdapt())
                    ) {
                        $aggression -= 1;
                    }
                    $this->settings->setAlgorithmAggression(strval($aggression));
                }
                $this->settings->selfPersist($this->simpleSettingsService);
            } else {
                $io->note('No statistics, nothing to do!');
            }
        } else {
            $io->note('Throttling turned off');
        }
        $io->success(sprintf('All done, now sleeping %s seconds before ending process...', $sleepSecondsAfterFinish));
        sleep($sleepSecondsAfterFinish);
        return Command::SUCCESS;
    }
}
