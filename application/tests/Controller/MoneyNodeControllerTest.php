<?php

declare(strict_types=1);

namespace App\Tests\Controller;

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
}
