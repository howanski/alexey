<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\NetworkUsageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'alexey:network:usage:record',
    description: 'Log Network State',
)]
class AlexeyNetworkUsageCommand extends Command
{
    private NetworkUsageService $service;

    private EntityManagerInterface $em;

    public function __construct(NetworkUsageService $service, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->service = $service;
        $this->em = $em;
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
        $stat = $this->service->getCurrentStatistic();
        $this->em->persist($stat);
        $this->em->flush($stat);
        $io->success(sprintf('All done, now sleeping %s seconds before ending process...', $sleepSecondsAfterFinish));
        sleep($sleepSecondsAfterFinish);
        return Command::SUCCESS;
    }
}
