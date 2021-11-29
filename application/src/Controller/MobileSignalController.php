<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\MobileSignalInfo;
use App\Service\SimpleCacheService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/network/usage/info-mobile-signal')]
final class MobileSignalController extends AbstractController
{
    #[Route('/', name: 'mobile_signal')]
    public function index(
        SimpleCacheService $cache,
    ): Response {
        $signalInfo = new MobileSignalInfo(cache: $cache);
        return $this->render('network_usage/mobile_signal.html.twig', [
            'signal_info' => $signalInfo,
        ]);
    }
}
