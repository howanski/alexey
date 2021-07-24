<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'alexey:user:new',
    description: 'Create new user',
)]
class AlexeyCreateUserCommand extends Command
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var UserPasswordHasherInterface
     */
    private $passwordHasher;

    public function __construct(ManagerRegistry $doctrine, UserPasswordHasherInterface $encoder)
    {
        $this->em = $doctrine->getManager();

        $this->userRepository = $this->em->getRepository(User::class);

        $this->passwordHasher = $encoder;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = '';
        while ((!$this->isStringOk($output, $username)) || (!$this->isUsernameUnique($output, $username))) {
            $question = new Question('Username: ');
            $helper = $this->getHelper('question');
            $username = $helper->ask($input, $output, $question);
        }

        $password = '';
        while (!$this->isStringOk($output, $password)) {
            $question = new Question('Password for user ' . $username . ': ');
            $helper = $this->getHelper('question');
            $password = $helper->ask($input, $output, $question);
        }

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $this->em->persist($user);
        $this->em->flush();
        $output->writeln('------ [ USER ' . $username . ' CREATED ] ------');
        return Command::SUCCESS;
    }

    private function isStringOk(OutputInterface $output, $authString): bool
    {
        $authString = trim($authString);
        if (empty($authString)) {
            return false;
        }
        if (preg_match('/[^a-z_\-0-9]/i', $authString)) {
            $output->writeln('Illegal characters used!');
            return false;
        }
        return true;
    }

    private function isUsernameUnique(OutputInterface $output, $username): bool
    {
        $existingUser = $this->userRepository->findOneBy(['username' => $username]);
        if (!empty($existingUser)) {
            $output->writeln('Username ' . $username . ' already taken');
            return false;
        }
        return true;
    }
}
