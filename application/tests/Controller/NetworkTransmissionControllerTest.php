<?php

declare(strict_types=1);

namespace App\Tests\Controller;

final class NetworkTransmissionControllerTest extends ControllerTestStub
{
    public function testAccess(): void
    {
        $this->testSecurityEnabled(path: '/network/transmission/');
    }

    public function testIndex(): void
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/network/transmission/');
        $this->assertResponseIsSuccessful();
    }
}
