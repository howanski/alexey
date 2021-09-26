<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManager;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ControllerTestStub extends WebTestCase
{
    protected function getClientWithLoggedInUser(string $username = 'test_user'): KernelBrowser
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername($username);
        $client->loginUser($testUser);
        return $client;
    }

    protected function getEntityManager(): EntityManager
    {
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.default_entity_manager');
        return $em;
    }

    protected function testSecurityEnabled(string $path): void
    {
        $client = static::createClient();
        $client->request('GET', $path);
        $this->assertResponseRedirects('/login');
    }
}
