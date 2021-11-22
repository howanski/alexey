<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\MoneyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class MoneyGraphController extends AbstractController
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
            $data = $service->getDataForChart(user: $this->getUser());
            return new JsonResponse($data);
        } else {
            return $this->redirectToRoute(route: 'dashboard');
        }
    }

    #[Route('/money/graph/forecast-data', name: 'money_graph_forecast_data')]
    public function forecastData(MoneyService $service, Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            $data = $service->getDataForForecastChart(user: $this->getUser());
            return new JsonResponse($data);
        } else {
            return $this->redirectToRoute(route: 'dashboard');
        }
    }
}
