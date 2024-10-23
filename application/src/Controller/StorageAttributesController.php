<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class StorageAttributesController extends AlexeyAbstractController
{
    #[Route('/storage/attributes', name: 'storage_attributes_index')]
    public function index(): Response
    {
        $this->addFlash(type: 'nord11', message: 'WIP :P');

        return $this->redirectToRoute('storage_index');
    }
}
