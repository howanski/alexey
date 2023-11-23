<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\NetworkChartType;
use App\Service\NetworkUsageService;
use Symfony\Component\HttpFoundation\Request;
use App\Form\NetworkUsageProviderSettingsType;
use App\Service\AlexeyTranslator;
use App\Service\NetworkUsageProviderSettings;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouterInterface;

#[Route('/network/usage')]
final class NetworkUsageController extends AbstractController
{
    #[Route('/info/{chartType}', name: 'network_usage')]
    public function index(
        RouterInterface $routerInterface,
        string $chartType = NetworkChartType::CHART_TYPE_MINUTES_TEN
    ): Response {
        $typeSettings = ['chartType' => $chartType];
        $form = $this->createForm(NetworkChartType::class, $typeSettings);
        $chartRoutes = [];
        foreach (NetworkChartType::CHART_TYPES as $possibleChartType) {
            $chartRoutes[$possibleChartType] = $routerInterface->generate(
                'network_usage',
                [
                    'chartType' => $possibleChartType
                ]
            );
        }

        if (NetworkChartType::CHART_TYPE_MINUTES_TEN === $chartType) {
            $chartRefresh = 5;
        } else {
            $chartRefresh = 45;
        }

        return $this->render('network_usage/index.html.twig', [
            'chart_selector_form' => $form->createView(),
            'chart_routes' => $chartRoutes,
            'chart_type' => $chartType,
            'chartRefresh' => $chartRefresh,
        ]);
    }

    #[Route('/settings', name: 'network_usage_settings')]
    public function settings(
        AlexeyTranslator $translator,
        NetworkUsageProviderSettings $settings,
        Request $request,
    ): Response {
        $form = $this->createForm(
            NetworkUsageProviderSettingsType::class,
            $settings
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $settings->selfPersist();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));
            return $this->redirectToRoute('network_usage', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('network_usage/settings.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/chart-data/{chartType}', name: 'network_usage_chart_data')]
    public function ajaxChartData(string $chartType, NetworkUsageService $service, Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            $data = $service->getDataForChart(
                chartDataType: $chartType,
                locale: $request->getLocale(),
            );
            return new JsonResponse($data);
        } else {
            return $this->redirectToRoute(route: 'dashboard');
        }
    }

    #[Route('/card-data/{property}', name: 'network_usage_card_data')]
    public function ajaxCardData(string $property, NetworkUsageService $service, Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            $cardData = $service->getDynacard(property: $property, locale: $request->getLocale());
            return $cardData->toResponse();
        } else {
            return $this->redirectToRoute(route: 'dashboard');
        }
    }

    #[Route('/force-router-reset', name: 'network_usage_force_router_reset')]
    public function forceRouterReset(NetworkUsageService $service): Response
    {
        $service->resetMikrotik();
        $this->addFlash(type: 'nord14', message: 'RESET!');
        return $this->redirectToRoute(route: 'network_usage');
    }
}
