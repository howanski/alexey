<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Currency;
use App\Entity\User;
use App\Form\CurrencyType;
use App\Repository\CurrencyRepository;
use App\Service\AlexeyTranslator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/money/node/currency')]
final class MoneyCurrencyController extends AbstractController
{
    #[Route('/list', name: 'currencies', methods: ['GET'])]
    public function index(
        CurrencyRepository $currencyRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $currencies = $currencyRepository->getUserCurrencies($user);
        return $this->render('currencies/index.html.twig', [
            'currencies' => $currencies,
        ]);
    }

    #[Route('/new', name: 'currency_new')]
    public function new(
        Request $request,
        AlexeyTranslator $translator,
        EntityManagerInterface $em,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $currency = new Currency($user);
        $form = $this->createForm(type: CurrencyType::class, data: $currency);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($currency);
            $em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));
            return $this->redirectToRoute(
                route: 'currencies',
                status: Response::HTTP_SEE_OTHER,
            );
        }

        return $this->renderForm('currencies/new.html.twig', [
            'currency' => $currency,
            'form' => $form,
        ]);
    }
}
