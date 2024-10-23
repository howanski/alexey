<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AlexeyAbstractController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em
    ) {
    }

    final public function alexeyUser(): ?User
    {
        return $this->getUser();
    }

    final public function fetchEntityById(string $className, int $id): ?object
    {
        return $this->em->getRepository($className)->find($id);
    }
}
