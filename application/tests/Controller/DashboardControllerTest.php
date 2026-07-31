<?php

declare(strict_types=1);

namespace App\Tests\Controller;

final class DashboardControllerTest extends ControllerTestStub
{
    public function testAccess(): void
    {
        $this->testSecurityEnabled(path: '/');
    }

    public function testAccessPing(): void
    {
        $this->testSecurityEnabled(path: '/ping');
    }

    public function testDashboard(): void
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }

    public function testPing(): void
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/ping');
        $this->assertResponseRedirects(expectedLocation: '/');
    }

    public function testPingJson()
    {
        $client = $this->getClientWithLoggedInUser(forAjaxRequest: true);
        $client->request('GET', '/ping');
        $this->assertResponseIsSuccessful();
    }
}
