<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\MoneyService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MoneyGraphController extends AlexeyAbstractController
{
    #[Route('/money/graph/nodes', name: 'money_graph_nodes')]
    public function nodes(): Response
    {
        return $this->render('money_graph/nodes.html.twig');
    }

    #[Route('/money/graph/nodes-data', name: 'money_graph_nodes_data')]
    public function data(MoneyService $service, Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            $user = $this->alexeyUser();
            $data = $service->getDataForChart(user: $user);
            return new JsonResponse($data);
        } else {
            return $this->redirectToRoute(route: 'dashboard');
        }
    }

    #[Route('/money/graph/forecast-data', name: 'money_graph_forecast_data')]
    public function forecastData(MoneyService $service, Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            $user = $this->alexeyUser();
            $data = $service->getDataForForecastChart(user: $user);
            return new JsonResponse($data);
        } else {
            return $this->redirectToRoute(route: 'dashboard');
        }
    }
}
