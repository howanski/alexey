<?php

declare(strict_types=1);

namespace App\Tests\Controller;

final class MoneyTransferControllerTest extends ControllerTestStub
{
    public function testAccess(): void
    {
        $this->testSecurityEnabled(path: '/money/transfer/');
    }

    public function testListOpens()
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/money/transfer/');
        $this->assertResponseIsSuccessful();
    }
}
