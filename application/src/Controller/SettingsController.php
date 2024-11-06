<?php

declare(strict_types=1);

namespace App\Controller;

use App\Class\OpenWeatherOneApiResponse;
use App\EventSubscriber\UserLocaleSubscriber;
use App\Form\SystemSettingsType;
use App\Form\UserSettingsType;
use App\Model\SystemSettings;
use App\Service\AlexeyTranslator;
use App\Service\RedditReader;
use App\Service\SimpleCacheService;
use App\Service\SimpleSettingsService;
use App\Service\TunnelInfoProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// TODO: Too much logic, move some garbage to service
final class SettingsController extends AlexeyAbstractController
{
    #[Route('/settings/user', name: 'settings_user')]
    public function settingsUser(
        AlexeyTranslator $translator,
        Request $request,
        SimpleCacheService $cacheService,
        SimpleSettingsService $simpleSettingsService,
    ): Response {
        $user = $this->alexeyUser();
        $settings = [
            'locale' => $user->getLocale(),
            'email' => $user->getEmail(),
            'redditUsername' => $simpleSettingsService->getSettings([
                RedditReader::REDDIT_USERNAME
            ], $user)[RedditReader::REDDIT_USERNAME],
            'redditStreamAutohide' => $simpleSettingsService->getSettings([
                RedditReader::REDDIT_EMPTY_STREAM_AUTOHIDE
            ], $user)[RedditReader::REDDIT_EMPTY_STREAM_AUTOHIDE],
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
            $simpleSettingsService->saveSettings(
                [RedditReader::REDDIT_USERNAME => strval($settings['redditUsername'])],
                $user
            );
            $simpleSettingsService->saveSettings(
                [RedditReader::REDDIT_EMPTY_STREAM_AUTOHIDE => strval($settings['redditStreamAutohide'])],
                $user
            );
            $this->em->persist($user);
            $this->em->flush();
            $cacheService->invalidateCache(OpenWeatherOneApiResponse::WEATHER_CACHE_KEY);
            $request->getSession()->set(UserLocaleSubscriber::USER_LOCALE, $user->getLocale());
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));
            return $this->redirectToRoute('settings_user', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('settings/user.html.twig', [
            'form' => $form,
            'pills' => $this->getMenuPills(true),
        ]);
    }

    #[Route('/settings/system', name: 'settings_system')]
    public function settingsSystem(
        AlexeyTranslator $t,
        Request $request,
        SimpleSettingsService $ss,
        TunnelInfoProvider $tunnelInfoProvider,
    ): Response {
        $settings = new SystemSettings(em: $this->em, translator: $t, simpleSettingsService: $ss);
        $form = $this->createForm(
            SystemSettingsType::class,
            $settings
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $settings = $form->getData();
            $settings->save();
            $this->addFlash(type: 'nord14', message: $t->translateFlash('saved'));
            return $this->redirectToRoute('settings_system', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('settings/system.html.twig', [
            'form' => $form,
            'pills' => $this->getMenuPills(false),
            'tunnel' => $tunnelInfoProvider->getCurrentTunnel(),
        ]);
    }

    public function __construct(
        private AlexeyTranslator $translator,
        protected EntityManagerInterface $em,
    ) {
    }

    private function getMenuPills(bool $isUserActive): array
    {
        // TODO: refactor pills globally
        return [[
            'name' => $this->translator->translateString(translationId: 'user_settings', module: 'settings'),
            'path' => $this->generateUrl(route: 'settings_user'),
            'active' => (true === $isUserActive),
        ], [
            'name' => $this->translator->translateString(translationId: 'system_settings', module: 'settings'),
            'path' => $this->generateUrl(route: 'settings_system'),
            'active' => (false === $isUserActive),
        ]];
    }
}
