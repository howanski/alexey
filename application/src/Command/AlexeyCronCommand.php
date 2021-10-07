<?php

declare(strict_types=1);

namespace App\Command;

use Carbon\Carbon;
use App\Entity\CronJob;
use App\Message\AsyncJob;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'alexey:cron',
    description: 'background timed Messenger jobs invoker',
)]
class AlexeyCronCommand extends Command
{
    private const DEFAULT_JOBS = [
        AsyncJob::TYPE_PING => 30,
        AsyncJob::TYPE_UPDATE_NETWORK_STATS => 30,
        AsyncJob::TYPE_TRANSMISSION_SPEED_ADJUST => 50,
        AsyncJob::TYPE_CLEANUP_NETWORK_STATS => 21600,
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Checking if default jobs are present...');
        $this->ensureDefaultJobsCreated();
        $output->writeln('Running in background...');
        for ($i = 0; $i < 720; $i++) {
            $this->runCronJobs();
            sleep(5);
            $memUsage = memory_get_usage(true);
            if ($memUsage > 67108864) {
                $output->writeln('Closing after exceeding 64 MB of RAM...');
                return Command::SUCCESS;
            }
        }
        $output->writeln('Closing after hour of looping...');
        return Command::SUCCESS;
    }

    private function runCronJobs()
    {
        $cronJobs = $this->em->getRepository(CronJob::class)->findAll();
        foreach ($cronJobs as $cronJob) {
            $this->runCronJob($cronJob);
        }
    }

    private function runCronJob(CronJob $cronJob)
    {
        $runEvery = $cronJob->getRunEvery();
        if ($cronJob->getIsActive() && $runEvery > 0) {
            $now = new Carbon('now');
            $lastRun = $cronJob->getLastRun();
            $readyToRun = false;
            if (is_null($lastRun)) {
                $readyToRun = true;
            } else {
                $nextRun =  (new Carbon($lastRun))->addSeconds($runEvery);
                $readyToRun =  ($nextRun <= $now);
            }
            if (true === $readyToRun) {
                $message = new AsyncJob($cronJob->getJobType());
                $this->bus->dispatch($message);
                $cronJob->setLastRun($now);
                $this->em->persist($cronJob);
                $this->em->flush();
            }
        }
    }

    private function ensureDefaultJobsCreated()
    {
        $repository = $this->em->getRepository(CronJob::class);
        foreach (self::DEFAULT_JOBS as $defaultType => $defaultRepeatTime) {
            $entity = $repository->findOneBy(['jobType' => $defaultType]);
            if (is_null($entity)) {
                $entity = new CronJob();
                $entity->setIsActive(true);
                $entity->setJobType($defaultType);
                $entity->setRunEvery($defaultRepeatTime);
                $this->em->persist($entity);
            }
        }
        $this->em->flush();
    }
}
