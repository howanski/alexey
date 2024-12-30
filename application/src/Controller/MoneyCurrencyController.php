<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Currency;
use App\Form\CurrencyType;
use App\Repository\CurrencyRepository;
use App\Service\AlexeyTranslator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/money/node/currency')]
final class MoneyCurrencyController extends AlexeyAbstractController
{
    #[Route('/list', name: 'currencies', methods: ['GET'])]
    public function index(
        CurrencyRepository $currencyRepository,
    ): Response {
        $user = $this->alexeyUser();
        $currencies = $currencyRepository->getUserCurrencies($user);
        return $this->render('currencies/index.html.twig', [
            'currencies' => $currencies,
        ]);
    }

    #[Route('/new', name: 'currency_new')]
    public function new(
        Request $request,
        AlexeyTranslator $translator,
    ): Response {
        $user = $this->alexeyUser();
        $currency = new Currency($user);
        $form = $this->createForm(type: CurrencyType::class, data: $currency);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($currency);
            $this->em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));
            return $this->redirectToRoute(
                route: 'currencies',
                status: Response::HTTP_SEE_OTHER,
            );
        }

        return $this->render('currencies/new.html.twig', [
            'currency' => $currency,
            'form' => $form,
        ]);
    }
}
