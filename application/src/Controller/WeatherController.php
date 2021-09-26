<?php

declare(strict_types=1);

namespace App\Controller;

use App\Class\WeatherSettings;
use App\Service\WeatherService;
use App\Form\WeatherSettingsType;
use App\Service\SimpleCacheService;
use App\Service\SimpleSettingsService;
use App\Class\OpenWeatherOneApiResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/weather')]
class WeatherController extends AbstractController
{
    #[Route('/', name: 'weather')]
    public function index(WeatherService $weatherService, RouterInterface $routerInterface): Response
    {
        $data = $weatherService->getWeather();
        return $this->render('weather/index.html.twig', [
            'weather_data' => $data,
            'chart_data_src' => $routerInterface->generate('weather_chart_data'),
        ]);
    }

    #[Route('/chart-data', name: 'weather_chart_data')]
    public function chartData(WeatherService $weatherService, Request $request): Response
    {
        $data = $weatherService->getChartData(locale: $request->getLocale());
        return new JsonResponse($data);
    }

    #[Route('/settings', name: 'weather_settings')]
    public function settings(
        Request $request,
        SimpleSettingsService $simpleSettingsService,
        SimpleCacheService $simpleCacheService
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
            $this->addFlash('success', 'app.flashes.saved');
            return $this->redirectToRoute('weather', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('weather/settings.html.twig', [
            'form' => $form,
        ]);
    }
}
