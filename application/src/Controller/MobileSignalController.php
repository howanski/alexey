<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\NetworkUsageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/network/usage/info-mobile-signal')]
final class MobileSignalController extends AbstractController
{
    #[Route('/', name: 'mobile_signal')]
    public function index(
        NetworkUsageService $service,
    ): Response {
        return $this->render('network_usage/mobile_signal.html.twig', [
            'signal_info' => $service->getMobileSignalInfo(),
        ]);
    }
}
