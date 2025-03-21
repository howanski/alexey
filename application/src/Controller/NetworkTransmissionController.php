<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\TransmissionSettingsType;
use App\Model\TransmissionSettings;
use App\Service\AlexeyTranslator;
use App\Service\SimpleSettingsService;
use App\Service\TransmissionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/network/transmission')]
final class NetworkTransmissionController extends AlexeyAbstractController
{
    #[Route('/', name: 'network_transmission')]
    public function index(): Response
    {
        return $this->render('network_transmission/index.html.twig');
    }

    #[Route('/settings', name: 'network_transmission_settings')]
    public function settings(
        Request $request,
        SimpleSettingsService $simpleSettingsService,
        AlexeyTranslator $translator,
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
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));
            return $this->redirectToRoute('network_transmission', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('network_transmission/settings.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/simulation/{type}', name: 'network_transmission_simulation')]
    public function simulation(string $type, TransmissionService $transmissionService, Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($transmissionService->getSimulationChartData(type: $type));
        } else {
            return $this->redirectToRoute(route: 'dashboard');
        }
    }
}
