<?php

declare(strict_types=1);

namespace App\Service;

use App\Class\ApiResponse;
use App\Entity\User;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouterInterface;

final class MobileApi
{

    public function __construct(
        private RouterInterface $router,
    ) {
    }

    public const API_FUNCTION_DASHBOARD = 'dashboard';
    private const API_FUNCTION_MACHINES = 'machines';

    private const API_FUNCTIONS = [
        self::API_FUNCTION_DASHBOARD => 'getDashboard',
        self::API_FUNCTION_MACHINES => 'getMachines',
    ];

    public function processFunction(
        User $user,
        string $functionName,
        array $parameters = [],
    ): JsonResponse {
        try {
            return call_user_func(
                [
                    $this,
                    self::API_FUNCTIONS[$functionName],
                ],
                $user,
                $parameters,
            );
        } catch (Exception $e) {
            $errorResponse = new ApiResponse();
            $errorResponse->setCode($e->getCode());
            $errorResponse->setMessage($e->getMessage());
            return $errorResponse->toResponse();
        }
    }

    private function getDashboard(User $user, array $parameters): JsonResponse
    {
        $response = new ApiResponse();
        $response->addText('Hi, ' . $user->getUserIdentifier() . ' !!!');
        $response->addText('');
        $response->setRefreshInSeconds(15);

        $now = new \DateTime('now');
        $response->addText('You called me on ' . $now->format('Y.m.d H:i:s'));

        $response->addButton(
            name: 'Machines',
            path: $this->router->generate(
                name: 'api',
                parameters: [
                    'function' => self::API_FUNCTION_MACHINES
                ]
            )
        );

        return $response->toResponse();
    }

    private function getMachines(User $user, array $parameters)
    {
        $response = new ApiResponse();
        $response->addText('Not implemented yet ;P');
        $response->addText('');
        $response->setRefreshInSeconds(15);

        return $response->toResponse();
    }
}
