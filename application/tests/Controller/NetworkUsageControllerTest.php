<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class NetworkUsageControllerTest extends WebTestCase
{
    public function testSecurityEnabled(): void
    {
        $client = static::createClient();
        $client->request('GET', '/network/usage/info');
        $this->assertResponseRedirects('/login');
    }

    public function testIndex(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('test_user');
        $client->loginUser($testUser);
        $client->request('GET', '/network/usage/info');
        $this->assertResponseIsSuccessful();
    }
}
