<?php

declare(strict_types=1);

namespace App\Controller;

use App\Class\TransmissionSettings;
use App\Service\NetworkUsageService;
use App\Form\TransmissionSettingsType;
use App\Service\SimpleSettingsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/network/transmission')]
class NetworkTransmissionController extends AbstractController
{
    #[Route('/', name: 'network_transmission')]
    public function index(Request $request, SimpleSettingsService $simpleSettingsService, NetworkUsageService $networkUsageService): Response
    {
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
            )
        ]);
    }
}
