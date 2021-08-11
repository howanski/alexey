<?php

namespace App\Controller;

use App\Form\WeatherSettingsType;
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
    public function settings(Request $request, WeatherService $weatherService): Response
    {
        $settings = $weatherService->getWeatherSettings();
        $form = $this->createForm(
            WeatherSettingsType::class,
            $settings
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $weatherService->setWeatherSettings($settings);
            return $this->redirectToRoute('weather', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('weather/settings.html.twig', [
            'form' => $form,
        ]);
    }
}
