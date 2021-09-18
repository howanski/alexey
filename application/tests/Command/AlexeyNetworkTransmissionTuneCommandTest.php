<?php

declare(strict_types=1);

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers App\Command\AlexeyNetworkTransmissionTuneCommand
 */
final class AlexeyNetworkTransmissionTuneCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('alexey:network:transmission:tune');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'sleepSecondsAfterFinish' => '1',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('[NOTE] Throttling turned off', $output);
    }
}
