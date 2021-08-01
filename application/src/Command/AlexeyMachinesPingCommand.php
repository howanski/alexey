<?php

namespace App\Command;

use App\Entity\NetworkMachine;
use App\Repository\NetworkMachineRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use JJG\Ping;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'alexey:machines:ping',
    description: 'Ping all networkMachines',
)]
class AlexeyMachinesPingCommand extends Command
{
    /**
     * @var NetworkMachineRepository
     */
    private $networkMachineRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->networkMachineRepository = $em->getRepository(NetworkMachine::class);
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

        $networkMachines = $this->networkMachineRepository->findAll();

        /**
         * @var NetworkMachine $networkMachine
         */
        foreach ($networkMachines as $networkMachine) {
            $uri = $networkMachine->getUri();
            $ping = new Ping($uri, 255, 2);
            $latency = $ping->ping();
            if ($latency !== false) {
                $networkMachine->setStatus(NetworkMachine::STATUS_REACHABLE);
                $now = new DateTime();
                $networkMachine->setLastSeen($now);
            } else {
                $networkMachine->setStatus(NetworkMachine::STATUS_UNREACHABLE);
            }
            $this->em->persist($networkMachine);
            $this->em->flush($networkMachine);
        }

        $io->success(sprintf('All done, now sleeping %s seconds before ending process...', $sleepSecondsAfterFinish));

        sleep($sleepSecondsAfterFinish);
        return Command::SUCCESS;
    }
}
