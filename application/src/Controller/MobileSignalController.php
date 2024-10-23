<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\MobileSignalInfo;
use App\Service\SimpleCacheService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/network/usage/info-mobile-signal')]
final class MobileSignalController extends AlexeyAbstractController
{
    #[Route('/', name: 'mobile_signal')]
    public function index(): Response
    {
        return $this->render('network_usage/mobile_signal.html.twig', []);
    }

    #[Route('/gauge/{stat}', name: 'mobile_signal_gauge', methods: ['GET'])]
    public function pieChartData(
        Request $request,
        SimpleCacheService $cache,
        string $stat,
    ): Response {
        if (false === $request->isXmlHttpRequest()) {
            return $this->redirectToRoute(route: 'dashboard');
        }

        $signalInfo = new MobileSignalInfo(cache: $cache);

        return $signalInfo->getAjaxGaugeData(gauge: $stat);
    }
}
