<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\MoneyService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class MoneyGraphController extends AbstractController
{
    #[Route('/money/graph/nodes', name: 'money_graph_nodes')]
    public function nodes(): Response
    {
        return $this->render('money_graph/nodes.html.twig');
    }

    #[Route('/money/graph/nodes-data', name: 'money_graph_nodes_data')]
    public function data(MoneyService $service): Response
    {
        $data = $service->getDataForChart(user: $this->getUser());
        return new JsonResponse($data);
    }

    #[Route('/money/graph/forecast-data', name: 'money_graph_forecast_data')]
    public function forecastData(MoneyService $service): Response
    {
        $data = $service->getDataForForecastChart(user: $this->getUser());
        return new JsonResponse($data);
    }
}
