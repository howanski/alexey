<?php

declare(strict_types=1);

namespace App\Tests\Controller;

final class WeatherControllerTest extends ControllerTestStub
{
    public function testAccess(): void
    {
        $this->testSecurityEnabled(path: '/weather/');
    }

    public function testIndex(): void
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/weather/');
        $this->assertResponseIsSuccessful();
    }
}
