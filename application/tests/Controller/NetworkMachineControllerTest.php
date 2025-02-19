<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\NetworkMachine;

final class NetworkMachineControllerTest extends ControllerTestStub
{
    public function testAccess(): void
    {
        $this->testSecurityEnabled(path: '/network/machines/');
    }

    public function testIndex(): void
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/network/machines/');
        $this->assertResponseIsSuccessful();
    }

    public function testNew(): int
    {
        $client = $this->getClientWithLoggedInUser();
        $client->followRedirects(true);
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
            needle: '<table class="items-center w-full bg-transparent border-collapse">',
            haystack: $response,
            message: '---!---> Can\'t find table (list) after new machine creation.',
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
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/network/machines/' . $machineId);
        $this->assertResponseIsSuccessful();
        return $machineId;
    }


    /**
     * @depends testShow
     */
    public function testEdit(int $machineId): int
    {
        $client = $this->getClientWithLoggedInUser();
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/network/machines/' . $machineId . '/edit');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form();
        $form['network_machine']['macAddress']->setValue('AA:BB:CC:DD:FF');

        $client->submit($form);

        $response = $client->getResponse()->getContent();

        $this->assertStringContainsString(
            needle: '<table class="items-center w-full bg-transparent border-collapse">',
            haystack: $response,
            message: '---!---> Can\'t find table/list after new machine creation.',
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
    public function testWake(int $machineId): int
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/network/machines/' . $machineId . '/wake/and-back-to/network_machine_index');
        $this->assertResponseRedirects('/network/machines/');
        return $machineId;
    }

    /**
     * @depends testWake
     */
    public function testDynaCard(int $machineId): int
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/network/machines/' . $machineId . '/card-data');
        $this->assertResponseRedirects(expectedLocation: '/');
        return $machineId;
    }

    /**
     * @depends testDynaCard
     */
    public function testDynaCardTwo(int $machineId): int
    {
        $client = $this->getClientWithLoggedInUser(forAjaxRequest: true);
        $client->request('GET', '/network/machines/' . $machineId . '/card-data');
        $this->assertResponseIsSuccessful();
        return $machineId;
    }

    /**
     * @depends testDynaCardTwo
     */
    public function testDelete(int $machineId): void
    {
        $client = $this->getClientWithLoggedInUser();
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/network/machines/' . $machineId);
        $this->assertResponseIsSuccessful();
        $crawler = $crawler->filter(selector: '.button-secure');
        $count = $crawler->count();
        $this->assertEquals(
            expected: 1,
            actual: $count,
            message: 'Too much secured buttons found',
        );
        $button = $crawler->eq(0);
        $csrf = $button->attr('data-csrf');
        $client->request(
            method: 'POST',
            uri: '/network/machines/' . $machineId,
            parameters: [
                '_token' => $csrf,
            ],
        );
        $this->assertResponseIsSuccessful();

        $em = $this->getEntityManager();
        $machines = $em->getRepository(entityName: NetworkMachine::class)->findAll();
        $this->assertCount(
            expectedCount: 0,
            haystack: $machines,
            message: 'Was machine deleted ?',
        );
    }
}
