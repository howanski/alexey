<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\StorageItem;
use App\Entity\StorageItemStack;
use App\Form\StorageItemAddQuantityType;
use App\Form\StorageItemMoveQuantityType;
use App\Form\StorageItemRemoveQuantityType;
use App\Form\StorageItemType;
use App\Repository\StorageItemRepository;
use App\Service\AlexeyTranslator;
use App\Service\StorageService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StorageItemController extends AlexeyAbstractController
{
    #[Route('/storage/item/list/{storageSpace}', name: 'storage_item_index')]
    public function index(
        StorageService $service,
        StorageItemRepository $storageItemRepository,
        int $storageSpace = 0,
    ): Response {
        $user = $this->alexeyUser();
        $storageItems = $storageItemRepository->findByUser(
            user: $user,
            storageSpaceId: $storageSpace,
        );

        if (count($storageItems) === 0 && $storageSpace > 0) {
            return $this->redirectToRoute(
                route: 'storage_item_index',
                parameters: [],
                status: Response::HTTP_SEE_OTHER,
            );
        }

        $templateData = $service->getTemplateDataForStorageSpaces($user);

        if (false === $templateData['userHasStorageSpaces']) {
            return $this->redirectToRoute(
                route: 'storage_index',
                parameters: [],
                status: Response::HTTP_SEE_OTHER,
            );
        }

        return $this->render(
            view: 'storage_item/index.html.twig',
            parameters: array_merge(
                $templateData,
                [
                    'storageItems' => $storageItems,
                    'storageSpaceFilter' => $storageSpace,
                ]
            ),
        );
    }

    #[Route('/storage/item/new', name: 'storage_item_new')]
    public function new(
        AlexeyTranslator $translator,
        Request $request,
    ): Response {
        $user = $this->alexeyUser();

        $item = new StorageItem();

        $form = $this->createForm(StorageItemType::class, $item, ['user' => $user, 'isNew' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $defaultStorageSpace = $form->get('storageSpace')->getData();
            $currentQuantity = $form->get('currentQuantity')->getData();

            $mappingStack = new StorageItemStack();
            $mappingStack->setQuantity($currentQuantity);
            $mappingStack->setStorageSpace($defaultStorageSpace);
            $mappingStack->setStorageItem($item);
            $this->em->persist($item);
            $this->em->persist($mappingStack);
            $this->em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));

            return $this->redirectToRoute(
                route: 'storage_item_index',
                parameters: [
                    'storageSpace' => $defaultStorageSpace->getId()
                ],
                status: Response::HTTP_SEE_OTHER,
            );
        }

        return $this->render('storage_item/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/storage/item/edit/{id}', name: 'storage_item_edit')]
    public function edit(
        int $id,
        AlexeyTranslator $translator,
        Request $request,
        StorageItemRepository $storageItemRepository,
    ): Response {
        $user = $this->alexeyUser();

        $item = $storageItemRepository->findByUser(user: $user, storageItemId: $id)[0];

        $form = $this->createForm(StorageItemType::class, $item, ['user' => $user, 'isNew' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));

            return $this->redirectToRoute(
                route: 'storage_item_edit',
                parameters: ['id' => $id],
                status: Response::HTTP_SEE_OTHER,
            );
        }

        return $this->render('storage_item/new.html.twig', [
            'form' => $form,
            'storageItem' => $item,
        ]);
    }

    #[Route('/storage/item/delete/{id}', name: 'storage_item_delete', methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
    ): Response {
        $storageItem = $this->fetchEntityById(className: StorageItem::class, id: $id);
        if ($this->isCsrfTokenValid('delete' . $storageItem->getId(), $request->request->get('_token'))) {
            $this->em->remove($storageItem);
            $this->em->flush();
        }

        return $this->redirectToRoute('storage_item_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/storage/item/add-quantity/{id}', name: 'storage_item_add_quantity')]
    public function addQuantity(
        AlexeyTranslator $translator,
        int $id,
        Request $request,
        StorageService $service,
    ): Response {
        $user = $this->alexeyUser();
        $storageItem = $this->fetchEntityById(className: StorageItem::class, id: $id);

        $form = $this->createForm(
            type: StorageItemAddQuantityType::class,
            data: null,
            options: [
                'user' => $user,
            ],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quantity = $form->get('quantity')->getData();
            $storageSpace = $form->get('storageSpace')->getData();

            $service->addQuantityToStorageItem(
                storageItem: $storageItem,
                targetStorageSpace: $storageSpace,
                quantity: $quantity,
            );

            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));

            return $this->redirectToRoute(
                route: 'storage_item_edit',
                parameters: [
                    'id' => $storageItem->getId()
                ],
                status: Response::HTTP_SEE_OTHER,
            );
        }

        return $this->render('storage_item/add_quantity.html.twig', [
            'form' => $form,
            'storageItem' => $storageItem,
        ]);
    }


    #[Route('/storage/item/move-quantity/{id}', name: 'storage_item_quantity_move')]
    public function moveQuantity(
        AlexeyTranslator $translator,
        int $id,
        Request $request,
        StorageService $service,
    ): Response {
        $user = $this->alexeyUser();
        $storageItemStack = $this->fetchEntityById(className: StorageItemStack::class, id: $id);

        $form = $this->createForm(
            type: StorageItemMoveQuantityType::class,
            data: null,
            options: [
                'user' => $user,
                'max' => $storageItemStack->getQuantity(),
            ],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quantity = $form->get('quantity')->getData();
            $storageSpace = $form->get('storageSpace')->getData();

            $service->moveQuantityToStorageSpace(
                originStack: $storageItemStack,
                targetStorageSpace: $storageSpace,
                quantity: $quantity,
            );

            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));

            return $this->redirectToRoute(
                route: 'storage_item_edit',
                parameters: [
                    'id' => $storageItemStack->getStorageItem()->getId()
                ],
                status: Response::HTTP_SEE_OTHER,
            );
        }

        return $this->render('storage_item/move_quantity.html.twig', [
            'form' => $form,
            'storageItem' => $storageItemStack->getStorageItem(),
            'storageSpace' => $storageItemStack->getStorageSpace(),
        ]);
    }

    #[Route('/storage/item/remove-quantity/{id}', name: 'storage_item_quantity_remove')]
    public function removeQuantity(
        AlexeyTranslator $translator,
        int $id,
        Request $request,
        StorageService $service,
    ): Response {
        $storageItemStack = $this->fetchEntityById(className: StorageItemStack::class, id: $id);
        $form = $this->createForm(
            type: StorageItemRemoveQuantityType::class,
            data: null,
            options: [
                'max' => $storageItemStack->getQuantity(),
            ],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quantity = $form->get('quantity')->getData();

            $service->removeQuantityFromStorageSpace(
                originStack: $storageItemStack,
                quantity: $quantity,
            );

            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));

            return $this->redirectToRoute(
                route: 'storage_item_edit',
                parameters: [
                    'id' => $storageItemStack->getStorageItem()->getId()
                ],
                status: Response::HTTP_SEE_OTHER,
            );
        }

        return $this->render('storage_item/remove_quantity.html.twig', [
            'form' => $form,
            'storageItem' => $storageItemStack->getStorageItem(),
            'storageSpace' => $storageItemStack->getStorageSpace(),
        ]);
    }
}
