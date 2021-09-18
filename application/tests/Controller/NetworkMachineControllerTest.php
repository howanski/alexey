<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers App\Controller\NetworkMachineController
 */
final class NetworkMachineControllerTest extends WebTestCase
{

    public function testSecurityEnabled(): void
    {
        $client = static::createClient();
        $client->request('GET', '/network/machines/');
        $this->assertResponseRedirects('/login');
    }

    public function testIndex(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('test_user');
        $client->loginUser($testUser);
        $client->request('GET', '/network/machines/');
        $this->assertResponseIsSuccessful();
    }
}
