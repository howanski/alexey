<?php

declare(strict_types=1);

namespace App\Tests\Controller;

final class MoneyGraphControllerTest extends ControllerTestStub
{
    public function testAccess(): void
    {
        $this->testSecurityEnabled(path: '/money/graph/nodes');
    }

    public function testListOpens(): void
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/money/graph/nodes');
        $this->assertResponseIsSuccessful();
    }

    public function testGraph(): void
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/money/graph/nodes-data');
        $this->assertResponseRedirects(expectedLocation: '/');
    }

    public function testGraphJson(): void
    {
        $client = $this->getClientWithLoggedInUser(forAjaxRequest: true);
        $client->request('GET', '/money/graph/nodes-data');
        $this->assertResponseIsSuccessful();
    }

    public function testForecast(): void
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/money/graph/forecast-data');
        $this->assertResponseRedirects(expectedLocation: '/');
    }

    public function testForecastJson(): void
    {
        $client = $this->getClientWithLoggedInUser(forAjaxRequest: true);
        $client->request('GET', '/money/graph/forecast-data');
        $this->assertResponseIsSuccessful();
    }
}
