<?php

declare(strict_types=1);

namespace App\Controller;

use App\Class\GaussianGauge;
use App\Model\MobileSignalInfo;
use App\Service\SimpleCacheService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
        #https://www.speedcheck.org/pl/wiki/rssi/
        #https://i0.wp.com/www.cablefree.net/wp-content/uploads/2016/04/LTE-RF-Conditions.png
        // TODO: SINR , RSRP, RSRQ, bonusPayload
        $data = [
            'rssi' => [
                'value' => $signalInfo->rssi,
                'optimum' => -40,
                'greenZoneWidth' => 27,
                'yellowZoneWidth' => 8,
            ],
        ];


        $gauge = new GaussianGauge(
            value: $data[$stat]['value'],
            optimum: $data[$stat]['optimum'],
            greenZoneWidth: $data[$stat]['greenZoneWidth'],
            yellowZoneWidth: $data[$stat]['yellowZoneWidth'],
        );

        return $gauge->getXmlResponse();
    }
}
