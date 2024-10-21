<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\StorageSpaceRepository;

final class StorageService
{
    public function __construct(
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
}
