<?php

declare(strict_types=1);

namespace App\Tests\Controller;

final class MobileSignalControllerTest extends ControllerTestStub
{
    public function testAccess(): void
    {
        $this->testSecurityEnabled(path: '/network/usage/info-mobile-signal/');
    }

    public function testPageOpens()
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/network/usage/info-mobile-signal/');
        $this->assertResponseIsSuccessful();
    }

    public function testRssiStat()
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/network/usage/info-mobile-signal/gauge/rssi');
        $this->assertResponseRedirects(expectedLocation: '/');
    }

    public function testRssiStatJson()
    {
        $client = $this->getClientWithLoggedInUser(forAjaxRequest: true);
        $client->request('GET', '/network/usage/info-mobile-signal/gauge/rssi');
        $this->assertResponseIsSuccessful();
    }
}
