<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\MobileApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
final class ApiController extends AbstractController
{
    #[Route('/{function}', name: 'api', defaults: ['function' => MobileApi::API_FUNCTION_DASHBOARD])]
    public function runner(
        Request $request,
        MobileApi $api,
        string $function,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        return $api->processFunction(
            user: $user,
            functionName: $function,
            parameters: $request->query->all(),
        );
    }
}
