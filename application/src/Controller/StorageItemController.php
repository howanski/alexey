<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\StorageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class StorageItemController extends AbstractController
{
    #[Route('/storage/item/list/{storageSpace}', name: 'storage_item_index')]
    public function index(StorageService $service, int $storageSpace = 0): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        return $this->render(
            'storage_item/index.html.twig',
            $service->getTemplateDataForStorageSpaces(user: $user),
        );
    }
}
