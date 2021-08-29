<?php

declare(strict_types=1);

namespace App\Controller;

use App\Class\TransmissionSettings;
use App\Service\NetworkUsageService;
use App\Form\TransmissionSettingsType;
use App\Service\SimpleSettingsService;
use App\Service\TransmissionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouterInterface;

#[Route('/network/transmission')]
class NetworkTransmissionController extends AbstractController
{
    #[Route('/', name: 'network_transmission')]
    public function index(
        Request $request,
        SimpleSettingsService $simpleSettingsService,
        NetworkUsageService $networkUsageService,
        RouterInterface $routerInterface,
    ): Response {
        $settings = new TransmissionSettings();
        $settings->selfConfigure($simpleSettingsService);
        $form = $this->createForm(
            TransmissionSettingsType::class,
            $settings
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $settings->selfPersist($simpleSettingsService);
            $this->addFlash('success', 'Saved!');
            return $this->redirectToRoute('network_transmission', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('network_transmission/settings.html.twig', [
            'form' => $form,
            'current' => $settings->getProposedThrottleSpeed(
                $networkUsageService->getLatestStatistic()->getTransferRateLeft()
            ),
            'chart_data_src' => $routerInterface->generate(name: 'network_transmission_simulation'),
        ]);
    }

    #[Route('/simulation', name: 'network_transmission_simulation')]
    public function simulation(TransmissionService $transmissionService): Response
    {
        return new JsonResponse($transmissionService->getSimulationChartData());
    }
}
