<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\MoneyNode;

final class MoneyNodeControllerTest extends ControllerTestStub
{
    public function testAccess(): void
    {
        $this->testSecurityEnabled(path: '/money/node/list');
    }

    public function testListOpens()
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/money/node/list');
        $this->assertResponseIsSuccessful();
    }

    public function testNew()
    {
        $client = $this->getClientWithLoggedInUser();
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/money/node/new');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form();
        $form['money_node']['name']->setValue('Some name');
        $form['money_node']['nodeType']->setValue(MoneyNode::NODE_TYPE_BANK_ACCOUNT);
        $form['money_node']['nodeGroup']->setValue(0); //default
        $form['money_node']['notes']->setValue('Notes here');
        $client->submit($form);


        $response = $client->getResponse()->getContent();

        $this->assertStringContainsString(
            needle: '<table class="items-center w-full bg-transparent border-collapse">',
            haystack: $response,
            message: '---!---> Can\'t find table (list) after new node creation.',
        );
    }
}
