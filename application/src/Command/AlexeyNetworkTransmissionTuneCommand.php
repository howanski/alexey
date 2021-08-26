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
    private NetworkUsageService $service;

    private TransmissionSettings $settings;

    public function __construct(NetworkUsageService $service, SimpleSettingsService $simpleSettingsService)
    {
        parent::__construct();
        $this->service = $service;
        $this->settings = new TransmissionSettings();
        $this->settings->selfConfigure($simpleSettingsService);
        $this->settings->selfPersist($simpleSettingsService);
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
            $stat = $this->service->getLatestStatistic();
            if ($stat instanceof NetworkStatistic) {
                $transmission = new Transmission($this->settings->getHost());
                $client = $transmission->getclient();
                $client->authenticate($this->settings->getUser(), $this->settings->getPassword());
                $session = $transmission->getSession();
                $speed = $this->settings->getProposedThrottleSpeed(
                    speedLeft: $stat->getTransferRateLeft(),
                    currentSpeed: $stat->getTotalSpeedFromReferencePoint() / 1024
                );
                $io->note('Setting ' . $speed . 'kBps');
                $session->setDownloadSpeedLimit($speed);
                $session->setAltSpeedDown($speed);
                $session->save();
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
