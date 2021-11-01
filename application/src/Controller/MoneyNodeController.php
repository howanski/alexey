<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\MoneyNode;
use App\Form\MoneyNodeType;
use App\Class\MoneyNodeSettings;
use App\Form\MoneyNodeSettingsType;
use App\Service\AlexeyTranslator;
use App\Service\SimpleSettingsService;
use App\Repository\MoneyNodeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/money/node')]
class MoneyNodeController extends AbstractController
{
    #[Route('/list', name: 'money_node_index', methods: ['GET'])]
    public function index(MoneyNodeRepository $moneyNodeRepository): Response
    {
        return $this->render('money_node/index.html.twig', [
            'money_nodes' => $moneyNodeRepository->getAllUserNodes($this->getUser()),
        ]);
    }

    #[Route('/new', name: 'money_node_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AlexeyTranslator $translator): Response
    {
        $moneyNode = new MoneyNode($this->getUser());
        $form = $this->createForm(MoneyNodeType::class, $moneyNode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($moneyNode);
            $entityManager->flush();
            $this->addFlash(type: 'success', message: $translator->translateFlash('saved'));
            return $this->redirectToRoute('money_node_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('money_node/new.html.twig', [
            'money_node' => $moneyNode,
            'form' => $form,
        ]);
    }

    #[Route('/show/{id}', name: 'money_node_show', methods: ['GET'])]
    public function show(MoneyNode $moneyNode): Response
    {
        //TODO: check user
        return $this->render('money_node/show.html.twig', [
            'money_node' => $moneyNode,
        ]);
    }

    #[Route('/edit/{id}', name: 'money_node_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MoneyNode $moneyNode, AlexeyTranslator $translator): Response
    {
        //TODO: check user
        $form = $this->createForm(MoneyNodeType::class, $moneyNode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(type: 'success', message: $translator->translateFlash('saved'));
            return $this->redirectToRoute('money_node_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('money_node/edit.html.twig', [
            'money_node' => $moneyNode,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'money_node_delete', methods: ['POST'])]
    public function delete(Request $request, MoneyNode $moneyNode, AlexeyTranslator $translator): Response
    {
        // TODO: SECURITY!
        if (true === $moneyNode->canBeDeleted()) {
            // if ($this->isCsrfTokenValid('delete' . $moneyNode->getId(), $request->request->get('_token'))) {
            //     $entityManager = $this->getDoctrine()->getManager();
            //     $entityManager->remove($moneyNode);
            //     $entityManager->flush();
            //     $this->addFlash(type: 'success', message: $translator->translateFlash('deleted'));
            // }
        } else {
            $this->addFlash(type: 'warning', message: $translator->translateFlash('delete_forbidden'));
        }


        return $this->redirectToRoute('money_node_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/settings', name: 'money_node_settings')]
    public function settings(
        Request $request,
        SimpleSettingsService $simpleSettingsService,
        AlexeyTranslator $translator,
    ): Response {
        $settings = new MoneyNodeSettings($this->getUser());
        $settings->selfConfigure($simpleSettingsService);
        $form = $this->createForm(
            MoneyNodeSettingsType::class,
            $settings
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $settings->selfPersist($simpleSettingsService);
            $this->addFlash(type: 'success', message: $translator->translateFlash('saved'));
            return $this->redirectToRoute('money_node_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('money_node/settings.html.twig', [
            'form' => $form,
        ]);
    }
}
