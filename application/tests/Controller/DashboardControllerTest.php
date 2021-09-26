<?php

declare(strict_types=1);

namespace App\Tests\Controller;

final class DashboardControllerTest extends ControllerTestStub
{
    public function testAccess(): void
    {
        $this->testSecurityEnabled(path: '/');
    }

    public function testDashboard()
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }
}
