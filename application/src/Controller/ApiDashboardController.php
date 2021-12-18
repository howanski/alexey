<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
final class ApiDashboardController extends AbstractController
{
    #[Route('/', name: 'api_dashboard')]
    public function index(): Response
    {
        return new JsonResponse('ok');
    }
}
