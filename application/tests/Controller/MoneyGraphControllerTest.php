<?php

declare(strict_types=1);

namespace App\Tests\Controller;

final class MoneyGraphControllerTest extends ControllerTestStub
{
    public function testAccess(): void
    {
        $this->testSecurityEnabled(path: '/money/graph/nodes');
    }

    public function testListOpens()
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/money/graph/nodes');
        $this->assertResponseIsSuccessful();
    }

    public function testGraph()
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/money/graph/nodes-data');
        $this->assertResponseRedirects(expectedLocation: '/');
    }

    public function testGraphJson()
    {
        $client = $this->getClientWithLoggedInUser(forAjaxRequest: true);
        $client->request('GET', '/money/graph/nodes-data');
        $this->assertResponseIsSuccessful();
    }

    public function testForecast()
    {
        $client = $this->getClientWithLoggedInUser();
        $client->request('GET', '/money/graph/forecast-data');
        $this->assertResponseRedirects(expectedLocation: '/');
    }

    public function testForecastJson()
    {
        $client = $this->getClientWithLoggedInUser(forAjaxRequest: true);
        $client->request('GET', '/money/graph/forecast-data');
        $this->assertResponseIsSuccessful();
    }
}
