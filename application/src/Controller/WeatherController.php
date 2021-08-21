<?php

declare(strict_types=1);

namespace App\Controller;

use App\Class\WeatherSettings;
use App\Form\WeatherSettingsType;
use App\Service\SimpleSettingsService;
use App\Service\WeatherService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/weather')]
class WeatherController extends AbstractController
{
    #[Route('/', name: 'weather')]
    public function index(WeatherService $weatherService): Response
    {
        $data = $weatherService->getCurrentWeather();
        return $this->render('weather/index.html.twig', [
            'weather_data' => $data
        ]);
    }

    #[Route('/settings', name: 'weather_settings')]
    public function settings(Request $request, SimpleSettingsService $simpleSettingsService): Response
    {
        $settings = new WeatherSettings();
        $settings->selfConfigure($simpleSettingsService);
        $form = $this->createForm(
            WeatherSettingsType::class,
            $settings
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $settings->selfPersist($simpleSettingsService);
            $this->addFlash('success', 'Saved!');
            return $this->redirectToRoute('weather', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('weather/settings.html.twig', [
            'form' => $form,
        ]);
    }
}
