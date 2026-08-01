<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Enum\FlashType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

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

    final public function flashSuccess(string $message): void
    {
        $this->addFlash(type: FlashType::SUCCESS->value, message: $message);
    }

    final public function flashError(string $message): void
    {
        $this->addFlash(type: FlashType::ERROR->value, message: $message);
    }

    final public function banishToLoginPage(): Response
    {
        return $this->redirectToRoute(
            route: 'app_login',
            parameters: [],
            status: Response::HTTP_SEE_OTHER,
        );
    }
}
