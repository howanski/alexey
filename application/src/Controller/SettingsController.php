<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\UserSettingsType;
use App\Service\SimpleCacheService;
use App\Class\OpenWeatherOneApiResponse;
use App\EventSubscriber\UserLocaleSubscriber;
use App\Form\SystemSettingsType;
use App\Model\SystemSettings;
use App\Service\AlexeyTranslator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// TODO: Too much logic, move some garbage to service
final class SettingsController extends AbstractController
{
    #[Route('/settings/user', name: 'settings_user')]
    public function settingsUser(
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
            return $this->redirectToRoute('settings_user', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('settings/user.html.twig', [
            'form' => $form,
            'pills' => $this->getMenuPills(true),
        ]);
    }

    #[Route('/settings/system', name: 'settings_system')]
    public function settingsSystem(EntityManagerInterface $em, AlexeyTranslator $t, Request $request): Response
    {
        $settings = new SystemSettings(em: $em, translator: $t);
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
        ]);
    }

    public function __construct(
        private AlexeyTranslator $translator,
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
