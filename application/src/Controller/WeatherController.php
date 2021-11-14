<?php

declare(strict_types=1);

namespace App\Controller;

use App\Class\WeatherSettings;
use App\Service\WeatherService;
use App\Form\WeatherSettingsType;
use App\Service\AlexeyTranslator;
use App\Service\SimpleCacheService;
use App\Service\SimpleSettingsService;
use App\Class\OpenWeatherOneApiResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/weather')]
class WeatherController extends AbstractController
{
    #[Route('/', name: 'weather')]
    public function index(): Response
    {
        return $this->render('weather/index.html.twig');
    }

    #[Route('/chart-data/{type}', name: 'weather_chart_data')]
    public function chartData(string $type, WeatherService $weatherService, Request $request): Response
    {
        $data = $weatherService->getChartData(locale: $request->getLocale(), type: $type);
        return new JsonResponse($data);
    }

    #[Route('/settings', name: 'weather_settings')]
    public function settings(
        Request $request,
        SimpleSettingsService $simpleSettingsService,
        SimpleCacheService $simpleCacheService,
        AlexeyTranslator $translator,
    ): Response {
        $settings = new WeatherSettings();
        $settings->selfConfigure($simpleSettingsService);
        $form = $this->createForm(
            WeatherSettingsType::class,
            $settings
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $settings->selfPersist($simpleSettingsService);
            $simpleCacheService->invalidateCache(OpenWeatherOneApiResponse::WEATHER_CACHE_KEY);
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));
            return $this->redirectToRoute('weather', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('weather/settings.html.twig', [
            'form' => $form,
        ]);
    }
}
