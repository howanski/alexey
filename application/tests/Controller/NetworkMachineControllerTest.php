<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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

    public function testNew(): void
    {
        $client = static::createClient();
        $client->followRedirects(true);
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('test_user');
        $client->loginUser($testUser);
        $crawler = $client->request('GET', '/network/machines/new');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form();
        $form['network_machine']['uri']->setValue('0.1.0.1');
        $form['network_machine']['name']->setValue('Test Machine');
        $form['network_machine']['macAddress']->setValue('AA:BB:CC:DD:EE');
        $form['network_machine']['wakeDestination']->setValue('ABCD');
        $form['network_machine']['showOnDashboard']->setValue(false);

        $client->submit($form);

        $response = $client->getResponse()->getContent();

        $this->assertStringContainsString(
            needle: '<h6 class="m-0 font-weight-bold text-primary">Machines status</h6>',
            haystack: $response,
            message: '---!---> Can\'t find table header after new machine creation.',
        );
    }

    // private function getEntityManager(): EntityManager
    // {
    //     $container = static::getContainer();
    //     $em = $container->get('doctrine.orm.default_entity_manager');
    //     return $em;
    // }
}
