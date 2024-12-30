<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\StorageItem;
use App\Entity\StorageItemStack;
use App\Entity\StorageSpace;
use App\Entity\User;
use App\Repository\StorageItemRepository;
use App\Repository\StorageSpaceRepository;
use Doctrine\ORM\EntityManagerInterface;

final class StorageService
{
    public function __construct(
        private EntityManagerInterface $em,
        private StorageItemRepository $storageItemRepository,
        private StorageSpaceRepository $storageSpaceRepository,
    ) {
    }

    public function getTemplateDataForStorageSpaces(User $user): array
    {
        $storageSpaces = $this->storageSpaceRepository->findByUser($user);
        return [
            'storageSpaces' => $storageSpaces,
            'userHasStorageSpaces' => count(value: $storageSpaces) > 0,
        ];
    }

    public function addQuantityToStorageItem(
        StorageItem $storageItem,
        StorageSpace $targetStorageSpace,
        int $quantity,
    ): void {
        $targetStack = null;
        foreach ($storageItem->getStacks() as $stack) {
            if ($stack->getStorageSpace() === $targetStorageSpace) {
                $targetStack = $stack;
            }
        }
        if (null === $targetStack) {
            $targetStack = new StorageItemStack();
            $targetStack->setQuantity(0);
            $targetStack->setStorageItem($storageItem);
            $targetStack->setStorageSpace($targetStorageSpace);
            $this->em->persist($targetStack);
        }

        $targetStack->setQuantity($targetStack->getQuantity() + $quantity);

        $this->em->flush();
    }

    public function moveQuantityToStorageSpace(
        StorageItemStack $originStack,
        StorageSpace $targetStorageSpace,
        int $quantity,
    ): void {
        $targetStack = null;
        $storageItem = $originStack->getStorageItem();
        foreach ($storageItem->getStacks() as $stack) {
            if ($stack->getStorageSpace() === $targetStorageSpace) {
                $targetStack = $stack;
            }
        }

        if ($targetStack === $originStack) {
            return;
        }

        if ($originStack->getQuantity() < $quantity) {
            return;
        }

        if (null === $targetStack) {
            $targetStack = new StorageItemStack();
            $targetStack->setQuantity(0);
            $targetStack->setStorageItem($storageItem);
            $targetStack->setStorageSpace($targetStorageSpace);
            $this->em->persist($targetStack);
        }

        $targetStack->setQuantity($targetStack->getQuantity() + $quantity);
        $originStack->setQuantity($originStack->getQuantity() - $quantity);

        if ($originStack->getQuantity() <= 0) {
            $this->em->remove($originStack);
        }

        $this->em->flush();
    }

    public function removeQuantityFromStorageSpace(StorageItemStack $originStack, int $quantity): void
    {
        $quantityLeft = $originStack->getQuantity();

        if ($quantityLeft < $quantity) {
            return;
        }

        $originStack->setQuantity($quantityLeft - $quantity);

        if ($quantityLeft === $quantity && $originStack->getStorageItem()->getStacks()->count() > 1) {
            $this->em->remove($originStack);
        }

        $this->em->flush();
    }

    public function getItemsWithMinimalQuantity(User $user): array
    {
        return $this->storageItemRepository->findByUser(
            user: $user,
            withMinimalQuantity: true
        );
    }
}
