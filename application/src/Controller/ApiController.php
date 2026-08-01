<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ApiDevice;
use App\Entity\User;
use App\Repository\ApiDeviceRepository;
use App\Security\ApiAuthenticator;
use App\Service\MobileApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use UnexpectedValueException;

#[Route('/api')]
final class ApiController extends AlexeyAbstractController
{
    #[Route('/{function}', name: 'api', defaults: ['function' => MobileApi::API_FUNCTION_DASHBOARD])]
    public function runner(
        ApiDeviceRepository $apiDeviceRepository,
        MobileApi $api,
        Request $request,
        string $function,
    ): Response {

        $user = $this->alexeyUser();
        if (!($user instanceof User)) {
            return $this->banishToLoginPage();
        }

        $secret = $request->headers->get(key: ApiAuthenticator::SECRET_HEADER, default: 'NOT_PROVIDED_ANY_SECRET');
        $apiDevice = $apiDeviceRepository->findOneBy(criteria: ['secret' => $secret]);
        if (!($apiDevice instanceof ApiDevice)) {
            throw new UnexpectedValueException();
        }

        return $api->processFunction(
            currentDevice: $apiDevice,
            functionName: $function,
            parameters: $request->query->all(),
            user: $user,
        );
    }
}
