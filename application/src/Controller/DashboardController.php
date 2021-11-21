<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DashboardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(DashboardService $service): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'dashboard_data' => $service->getDashboardData()
        ]);
    }

    #[Route('/ping', name: 'ping')]
    public function ping(Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse('pong');
        } else {
            return $this->redirectToRoute(route: 'dashboard');
        }
    }
}
