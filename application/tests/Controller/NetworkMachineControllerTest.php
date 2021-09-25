<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\NetworkMachine;
use Doctrine\ORM\EntityManager;
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

    public function testNew(): int
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

        $em = $this->getEntityManager();
        $machines = $em->getRepository(entityName: NetworkMachine::class)->findAll();
        $this->assertCount(
            expectedCount: 1,
            haystack: $machines,
            message: 'Was machine persisted ?',
        );
        return $machines[0]->getId();
    }

    /**
     * @depends testNew
     */
    public function testShow(int $machineId): int
    {
        $client = static::createClient();
        $client->followRedirects(true);
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('test_user');
        $client->loginUser($testUser);
        $crawler = $client->request('GET', '/network/machines/' . $machineId);
        $this->assertResponseIsSuccessful();
        return $machineId;
    }


    /**
     * @depends testShow
     */
    public function testEdit(int $machineId): int
    {
        $client = static::createClient();
        $client->followRedirects(true);
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('test_user');
        $client->loginUser($testUser);
        $crawler = $client->request('GET', '/network/machines/' . $machineId . '/edit');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Update')->form();
        $form['network_machine']['macAddress']->setValue('AA:BB:CC:DD:FF');

        $client->submit($form);

        $response = $client->getResponse()->getContent();

        $this->assertStringContainsString(
            needle: '<h6 class="m-0 font-weight-bold text-primary">Machines status</h6>',
            haystack: $response,
            message: '---!---> Can\'t find table header after new machine creation.',
        );

        $em = $this->getEntityManager();
        $machines = $em->getRepository(entityName: NetworkMachine::class)->findAll();
        $this->assertCount(
            expectedCount: 1,
            haystack: $machines,
            message: 'Was machine persisted ?',
        );

        $this->assertEquals(
            expected: 'AA:BB:CC:DD:FF',
            actual: $machines[0]->getMacAddress(),
            message: 'Mac didn\'t saved!',
        );
        return $machineId;
    }

    /**
     * @depends testEdit
     */
    public function testDelete(int $machineId)
    {
        $client = static::createClient();
        $client->followRedirects(true);
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('test_user');
        $client->loginUser($testUser);
        $crawler = $client->request('POST', '/network/machines/' . $machineId);
        $this->assertResponseIsSuccessful();

        $em = $this->getEntityManager();
        $machines = $em->getRepository(entityName: NetworkMachine::class)->findAll();
        // FIXME: add token so it will delete
        $this->assertCount(
            expectedCount: 1,
            haystack: $machines,
            message: 'Was machine deleted ?',
        );
    }

    private function getEntityManager(): EntityManager
    {
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.default_entity_manager');
        return $em;
    }
}
