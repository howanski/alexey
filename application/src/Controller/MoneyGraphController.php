<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\MoneyService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MoneyGraphController extends AbstractController
{
    #[Route('/money/graph/nodes', name: 'money_graph_nodes')]
    public function nodes(RouterInterface $routerInterface): Response
    {
        return $this->render('money_graph/nodes.html.twig', [
            'chart_data_src' => $routerInterface->generate('money_graph_nodes_data'),
        ]);
    }

    #[Route('/money/graph/nodes-data', name: 'money_graph_nodes_data')]
    public function data(MoneyService $service): Response
    {
        $data = $service->getDataForChart(user: $this->getUser());
        return new JsonResponse($data);
    }
}
