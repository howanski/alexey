<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\StorageSpace;
use App\Form\StorageSpaceType;
use App\Repository\StorageSpaceRepository;
use App\Service\AlexeyTranslator;
use App\Service\StorageService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StorageController extends AlexeyAbstractController
{
    #[Route('/storage', name: 'storage_index')]
    public function index(StorageService $service): Response
    {
        $user = $this->alexeyUser();
        return $this->render(
            'storage/index.html.twig',
            $service->getTemplateDataForStorageSpaces(user: $user),
        );
    }

    #[Route('/storage/space/new', name: 'storage_space_new')]
    public function add(
        AlexeyTranslator $translator,
        Request $request,
    ): Response {
        $user = $this->alexeyUser();
        $channel = new StorageSpace();
        $channel->setUser(user: $user);
        $form = $this->createForm(
            type: StorageSpaceType::class,
            data: $channel,
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($channel);
            $this->em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));

            return $this->redirectToRoute(
                route: 'storage_index',
                parameters: [],
                status: Response::HTTP_SEE_OTHER,
            );
        }
        return $this->render('storage/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/storage/space/edit/{id}', name: 'storage_space_edit')]
    public function edit(
        AlexeyTranslator $translator,
        int $id,
        Request $request,
        StorageSpaceRepository $storageSpaceRepository,
    ): Response {
        $user = $this->alexeyUser();

        $channel = $storageSpaceRepository->findOneBy(
            criteria: [
                'user' => $user,
                'id' => $id,
            ],
        );

        $form = $this->createForm(
            type: StorageSpaceType::class,
            data: $channel,
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($channel);
            $this->em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));

            return $this->redirectToRoute(
                route: 'storage_index',
                parameters: [],
                status: Response::HTTP_SEE_OTHER,
            );
        }
        return $this->render('storage/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/storage/space/delete/{id}', name: 'storage_space_delete')]
    public function show(
        AlexeyTranslator $translator,
        int $id,
        StorageSpaceRepository $storageSpaceRepository,
    ): Response {
        $user = $this->alexeyUser();
        /** @var StorageSpace */
        $storageSpace = $storageSpaceRepository->findOneBy(
            criteria: [
                'user' => $user,
                'id' => $id,
            ]
        );

        if ($storageSpace->hasStacks()) {
            $this->addFlash(type: 'nord11', message: $translator->translateFlash('delete_forbidden'));

            return $this->redirectToRoute(
                route: 'storage_index',
                parameters: [],
                status: Response::HTTP_SEE_OTHER,
            );
        }

        $this->em->remove($storageSpace);
        $this->em->flush();

        $this->addFlash(type: 'nord14', message: $translator->translateFlash('deleted'));
        return $this->redirectToRoute('storage_index');
    }
}
