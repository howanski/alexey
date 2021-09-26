<?php

declare(strict_types=1);

namespace App\Controller;

use App\EventSubscriber\UserLocaleSubscriber;
use App\Form\UserSettingsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

class SettingsController extends AbstractController
{
    #[Route('/settings', name: 'settings')]
    public function settings(Request $request, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        $settings = [
            'locale' => $user->getLocale(),
        ];
        $form = $this->createForm(
            UserSettingsType::class,
            $settings
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $settings = $form->getData();
            $user->setLocale($settings['locale']);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $request->getSession()->set(UserLocaleSubscriber::USER_LOCALE, $user->getLocale());
            $this->addFlash('success', $translator->trans('app.flashes.saved'));
            return $this->redirectToRoute('settings', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('settings/index.html.twig', [
            'form' => $form,
        ]);
    }
}
