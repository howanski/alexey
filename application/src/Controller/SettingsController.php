<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\UserSettingsType;
use App\Service\SimpleCacheService;
use App\Class\OpenWeatherOneApiResponse;
use App\EventSubscriber\UserLocaleSubscriber;
use App\Service\AlexeyTranslator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SettingsController extends AbstractController
{
    #[Route('/settings', name: 'settings')]
    public function settings(
        Request $request,
        AlexeyTranslator $translator,
        SimpleCacheService $cacheService,
    ): Response {
        $user = $this->getUser();
        $settings = [
            'locale' => $user->getLocale(),
            'email' => $user->getEmail(),
        ];
        $form = $this->createForm(
            UserSettingsType::class,
            $settings
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $settings = $form->getData();
            $user->setLocale($settings['locale']);
            $user->setEmail(strval($settings['email']));
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $cacheService->invalidateCache(OpenWeatherOneApiResponse::WEATHER_CACHE_KEY);
            $request->getSession()->set(UserLocaleSubscriber::USER_LOCALE, $user->getLocale());
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));
            return $this->redirectToRoute('settings', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('settings/index.html.twig', [
            'form' => $form,
        ]);
    }
}
