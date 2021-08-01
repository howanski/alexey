<?php

namespace App\Controller;

use App\Service\DashboardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(DashboardService $service): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'dashboard_data' => $service->getDashboardData()
        ]);
    }
}
