<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
final class ApiDashboardController extends AbstractController
{
    // TODO: make some response factory so CORS, status, message, XYZ? will always be there
    private const CORS_HEADERS = [
        'Access-Control-Allow-Origin' => '*',
    ];

    #[Route('/', name: 'api_index')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        return new JsonResponse(
            data: [
                'code' => 200,
                'message' => 'Connection success',
                'payload' => [
                    'user' => $user->getUserIdentifier(),
                ]
            ],
            status: 200,
            headers: self::CORS_HEADERS,
        );
    }
}
