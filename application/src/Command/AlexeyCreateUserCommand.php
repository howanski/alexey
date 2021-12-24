<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'alexey:user:new',
    description: 'Create new user',
)]
final class AlexeyCreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
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
        if (strlen($authString) === 0) {
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
        if ($existingUser instanceof User) {
            $output->writeln('Username ' . $username . ' already taken');
            return false;
        }
        return true;
    }
}
