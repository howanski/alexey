<?php

declare(strict_types=1);

namespace App\Controller;

use App\Class\DynamicCard;
use App\Class\OpenWeatherOneApiResponse;
use App\Form\WeatherSettingsType;
use App\Model\WeatherSettings;
use App\Service\AlexeyTranslator;
use App\Service\SimpleCacheService;
use App\Service\SimpleSettingsService;
use App\Service\WeatherService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/weather')]
final class WeatherController extends AlexeyAbstractController
{
    #[Route('/', name: 'weather')]
    public function index(): Response
    {
        return $this->render('weather/index.html.twig');
    }

    #[Route('/chart-data/{type}', name: 'weather_chart_data')]
    public function chartData(string $type, WeatherService $weatherService, Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            $data = $weatherService->getChartData(locale: $request->getLocale(), type: $type);
            return new JsonResponse($data);
        } else {
            return $this->redirectToRoute(route: 'dashboard');
        }
    }

    #[Route('/card-data/{daysAhead}', name: 'weather_card_data')]
    public function cardData(int $daysAhead, WeatherService $weatherService, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $weatherData = $weatherService->getWeather()->getWeatherReadable($request->getLocale());
            $dailyWeather = $weatherData['daily'][$daysAhead];
            $render = $this->renderView(
                view: 'weather/card_content.html.twig',
                parameters: [
                    'weather' => $dailyWeather,
                ],
            );

            $card = new DynamicCard();
            $card->setRawContent($render);
            return $card->toResponse();
        } else {
            return $this->redirectToRoute(route: 'dashboard');
        }
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

        return $this->render('weather/settings.html.twig', [
            'form' => $form,
        ]);
    }
}
