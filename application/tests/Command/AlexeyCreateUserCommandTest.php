<?php

declare(strict_types=1);

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class AlexeyCreateUserCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $kernel = static::createKernel();
        $this->assertSame('test', $kernel->getEnvironment()); // better safe than sorry
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $command = $application->find('alexey:user:new');

        // Test - credentials ok
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['user', 'password']);
        $commandTester->execute(['command' => $command->getName()]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(
            'Username: ' .
                'Password for user user: ' .
                '------ [ USER user CREATED ] ------',
            $output
        );

        // Test - user previously created
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['user', 'user2', 'password']);
        $commandTester->execute(['command' => $command->getName()]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Username user already taken', $output);
        $this->assertStringContainsString('USER user2 CREATED', $output);

        // Test - Illegal chars in username
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['user3!!', 'user3', 'password']);
        $commandTester->execute(['command' => $command->getName()]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Illegal characters used!', $output);
        $this->assertStringContainsString('USER user3 CREATED', $output);


        // Test - Illegal chars in password
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['user4', 'password!!', 'password']);
        $commandTester->execute(['command' => $command->getName()]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Illegal characters used!', $output);
        $this->assertStringContainsString('USER user4 CREATED', $output);
    }
}
