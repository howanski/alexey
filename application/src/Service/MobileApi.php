<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

final class MobileApi
{
    private const CORS_HEADERS = [
        'Access-Control-Allow-Origin' => '*',
    ];

    public const API_FUNCTION_DASHBOARD = 'dashboard';

    private const API_FUNCTIONS = [
        self::API_FUNCTION_DASHBOARD => 'getDashboard'
    ];

    public function processFunction(
        User $user,
        string $functionName,
        array $parameters = [],
    ): JsonResponse {
        try {
            return new JsonResponse(
                data: [
                    'code' => 200,
                    'message' => 'ok',
                    'payload' => call_user_func(
                        [
                            $this,
                            self::API_FUNCTIONS[$functionName],
                        ],
                        $user,
                        $parameters,
                    ),
                ],
                status: 200,
                headers: self::CORS_HEADERS,
            );
        } catch (Exception $e) {
            return new JsonResponse(
                data: [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'payload' => [],
                ],
                status: $e->getCode(),
                headers: self::CORS_HEADERS,
            );
        }
    }

    private function getDashboard(User $user, array $parameters): array
    {
        return [
            'this will be dashboard',
            $user->getEmail(),
        ];
    }
}
