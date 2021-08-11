<?php

namespace App\Controller;

use App\Service\NetworkUsageService;
use Symfony\Component\HttpFoundation\Request;
use App\Form\NetworkUsageProviderSettingsType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouterInterface;

#[Route('/network/usage')]
class NetworkUsageController extends AbstractController
{
    #[Route('/info/{chartType}', name: 'network_usage')]
    public function index(
        NetworkUsageService $networkUsageService,
        RouterInterface $routerInterface,
        string $chartType = NetworkUsageService::CHART_TYPE_SPEED_TODAY
    ): Response {
        return $this->render('network_usage/index.html.twig', [
            'data_current' => $networkUsageService->getCurrentStatistic(false),
            'chart_data_src' => $routerInterface->generate('network_usage_chart_data', [
                'chartType' => $chartType
            ])
        ]);
    }

    #[Route('/settings', name: 'network_usage_settings')]
    public function settings(Request $request, NetworkUsageService $networkUsageService): Response
    {
        $settings = $networkUsageService->getConnectionSettings();
        $form = $this->createForm(
            NetworkUsageProviderSettingsType::class,
            $settings
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $networkUsageService->saveConnectionSettings($settings);
            return $this->redirectToRoute('network_usage', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('network_usage/settings.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/chart-data/{chartType}', name: 'network_usage_chart_data')]
    public function ajaxChartData(string $chartType, NetworkUsageService $service): Response
    {
        $data = $service->getDataForChart($chartType);
        return new JsonResponse($data);
    }
}
