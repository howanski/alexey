<?php

declare(strict_types=1);

namespace App\Service;

use App\Class\ApiResponse;
use App\Entity\NetworkMachine;
use App\Entity\User;
use App\Message\AsyncJob;
use App\Repository\NetworkMachineRepository;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;

final class MobileApi
{

    public function __construct(
        private MessageBusInterface $bus,
        private NetworkMachineRepository $networkMachineRepository,
        private RouterInterface $router,
    ) {
    }

    public const API_FUNCTION_DASHBOARD = 'dashboard';
    private const API_FUNCTION_MACHINES = 'machines';
    private const API_FUNCTION_MACHINE_WAKE = 'machineWake';

    private const API_FUNCTIONS = [
        self::API_FUNCTION_DASHBOARD => 'getDashboard',
        self::API_FUNCTION_MACHINES => 'getMachines',
        self::API_FUNCTION_MACHINE_WAKE => 'wakeMachine',
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
        $response->addText('Hi, ' . $user->getUserIdentifier() . ' !');
        $response->addText('');

        $response->addButton(
            name: 'Machines',
            path: $this->router->generate(
                name: 'api',
                parameters: [
                    'function' => self::API_FUNCTION_MACHINES
                ]
            )
        );
        $response->addText('');
        return $response->toResponse();
    }

    private function getMachines(User $user, array $parameters)
    {
        $response = new ApiResponse();
        $machines = $this->networkMachineRepository->findBy(['showOnDashboard' => true]);
        /** @var NetworkMachine $machine */
        foreach ($machines as $machine) {
            $response->addText($machine->getName() . ': ' . $machine->getStatusReadable() . ' - '
                . $machine->getLastSeenReadable($user->getLocale()));
            if ($machine->canBeWoken()) {
                $response->addButton(
                    name: 'Wake',
                    path: $this->router->generate(
                        name: 'api',
                        parameters: [
                            'function' => self::API_FUNCTION_MACHINE_WAKE,
                            'id' => $machine->getId(),
                        ]
                    )
                );
            }
        }
        $response->setRefreshInSeconds(15);
        $response->addText('');

        return $response->toResponse();
    }

    private function wakeMachine(User $user, array $parameters)
    {
        $response = new ApiResponse();

        $machineId = $parameters['id'];

        $networkMachine = $this->networkMachineRepository->find($machineId);

        $payload = [
            'wakeDestination' => $networkMachine->getWakeDestination(),
            'macAddress' => $networkMachine->getMacAddress(),
        ];
        $message = new AsyncJob(
            jobType: AsyncJob::TYPE_WAKE_ON_LAN,
            payload: $payload,
        );

        $this->bus->dispatch($message);

        $response->addText('Signal sent');
        $response->addText('');
        $response->addButton(
            name: '<- Back',
            path: $this->router->generate(
                name: 'api',
                parameters: [
                    'function' => self::API_FUNCTION_MACHINES
                ]
            )
        );
        $response->addText('');

        return $response->toResponse();
    }
}
