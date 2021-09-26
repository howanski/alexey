<?php

declare(strict_types=1);

namespace App\Tests\Controller;

final class NetworkUsageControllerTest extends ControllerTestStub
{
    public function testAccess(): void
    {
        $this->testSecurityEnabled(path: '/network/usage/info');
    }

    public function testIndex(): void
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/network/usage/info');
        $this->assertResponseIsSuccessful();
    }
}
