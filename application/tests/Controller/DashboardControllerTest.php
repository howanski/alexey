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

    public function testDashboard()
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }

    public function testPing()
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/ping');
        $this->assertResponseRedirects(expectedLocation: '/');
    }

    public function testPingJson()
    {
        $client = $this->getClientWithLoggedInUser(forAjaxRequest: true);
        $client->setServerParameter(key: 'HTTP_X-Requested-With', value: 'XMLHttpRequest');
        $client->request('GET', '/ping');
        $this->assertResponseIsSuccessful();
    }
}
