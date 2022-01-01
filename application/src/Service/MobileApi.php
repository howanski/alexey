<?php

declare(strict_types=1);

namespace App\Service;

use App\Class\ApiResponse;
use App\Entity\NetworkMachine;
use App\Entity\NetworkStatistic;
use App\Entity\User;
use App\Message\AsyncJob;
use App\Model\TransmissionSettings;
use App\Repository\NetworkMachineRepository;
use App\Repository\NetworkStatisticRepository;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;

final class MobileApi
{
    public function __construct(
        private AlexeyTranslator $translator,
        private MessageBusInterface $bus,
        private NetworkMachineRepository $networkMachineRepository,
        private NetworkStatisticRepository $networkStatisticRepository,
        private RouterInterface $router,
        private SimpleSettingsService $simpleSettingsService,
        private WeatherService $weatherService,
    ) {
    }

    public const API_FUNCTION_DASHBOARD = 'dashboard';
    private const API_FUNCTION_MACHINES = 'machines';
    private const API_FUNCTION_MACHINE_WAKE = 'machineWake';
    private const API_FUNCTION_WEATHER = 'weather';
    private const API_FUNCTION_NETWORK_USAGE = 'networkUsage';

    private const API_FUNCTIONS = [
        self::API_FUNCTION_DASHBOARD => 'getDashboard',
        self::API_FUNCTION_MACHINES => 'getMachines',
        self::API_FUNCTION_MACHINE_WAKE => 'wakeMachine',
        self::API_FUNCTION_WEATHER => 'getWeather',
        self::API_FUNCTION_NETWORK_USAGE => 'getNetworkUsage',
    ];

    public function processFunction(
        User $user,
        string $functionName,
        array $parameters = [],
    ): JsonResponse {
        $this->translator->forceLocale($user->getLocale());
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
        $response->addText(
            $this->translator->translateString(
                translationId: 'hi',
                module: 'common'
            ) .
                ', ' . $user->getUserIdentifier() . ' !'
        );
        $response->addSpacer();

        $response->addButton(
            name: $this->translator->translateString(
                translationId: 'menu_record',
                module: 'network_machines'
            ),
            path: $this->router->generate(
                name: 'api',
                parameters: [
                    'function' => self::API_FUNCTION_MACHINES
                ]
            )
        );

        $response->addButton(
            name: $this->translator->translateString(
                translationId: 'menu_record',
                module: 'weather'
            ),
            path: $this->router->generate(
                name: 'api',
                parameters: [
                    'function' => self::API_FUNCTION_WEATHER
                ]
            )
        );

        $response->addButton(
            name: $this->translator->translateString(
                translationId: 'menu_record',
                module: 'network_usage'
            ),
            path: $this->router->generate(
                name: 'api',
                parameters: [
                    'function' => self::API_FUNCTION_NETWORK_USAGE
                ]
            )
        );

        return $response->toResponse();
    }

    private function getMachines(User $user, array $parameters)
    {
        $response = new ApiResponse();
        $machines = $this->networkMachineRepository->getNameOrdered();
        /** @var NetworkMachine $machine */
        foreach ($machines as $machine) {
            $response->addText($machine->getName());
            $response->addText(
                $this->translator->translateString(
                    translationId: strtolower($machine->getStatusReadable()),
                    module: 'network_machines'
                )
            );
            $response->addText($machine->getLastSeenReadable($user->getLocale()));
            if ($machine->canBeWoken()) {
                $response->addButton(
                    name: $this->translator->translateString(
                        translationId: 'wake',
                        module: 'network_machines'
                    ),
                    path: $this->router->generate(
                        name: 'api',
                        parameters: [
                            'function' => self::API_FUNCTION_MACHINE_WAKE,
                            'id' => $machine->getId(),
                        ]
                    )
                );
            }
            $response->addSpacer();
        }
        $response->setRefreshInSeconds(15);

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

        $response->addText($this->translator->translateFlash(
            translationId: 'signal_dispatched',
            module: 'common'
        ));
        $response->addText('');
        $response->addButton(
            name: '<- ' . $this->translator->translateString(
                translationId: 'back',
                module: 'common'
            ),
            path: $this->router->generate(
                name: 'api',
                parameters: [
                    'function' => self::API_FUNCTION_MACHINES
                ]
            )
        );
        $response->addSpacer();

        return $response->toResponse();
    }

    private function getWeather(User $user, array $parameters): JsonResponse
    {
        $response = new ApiResponse();


        $forecast = $this->weatherService->getWeather()->getWeatherReadable($user->getLocale());
        foreach ($forecast['daily'] as $weatherDay) {
            $response->addText($this->translator->translateTime(
                value: $weatherDay['date'],
                timeUnit: 'day',
                type: 'long'
            ));
            $response->addText($weatherDay['weather']);
            $response->addText($weatherDay['temperature'] . ' °C');
            $response->addSpacer();
        }

        return $response->toResponse();
    }

    private function getNetworkUsage(User $user, array $parameters): JsonResponse
    {
        $response = new ApiResponse();
        $response->addSpacer();
        $transmissionSettings = new TransmissionSettings();
        $transmissionSettings->selfConfigure($this->simpleSettingsService);

        $networkStatistic = $this->networkStatisticRepository->getLatestOne();
        if ($networkStatistic instanceof NetworkStatistic) {
            $label = $this->translator->translateString(translationId: 'optimal_speed', module: 'network_usage');
            $value = $networkStatistic->getTransferRateLeftReadable(
                precision: 4,
                frameWidth: $transmissionSettings->getTargetFrame()
            );
            $response->addText($label);
            $response->addText($value);
            $response->addSpacer();


            $label = $this->translator->translateString(translationId: 'traffic_left', module: 'network_usage');
            $value = $networkStatistic->getTrafficLeftReadable(
                precision: 4,
                frameWidth: $transmissionSettings->getTargetFrame(),
            );
            $response->addText($label);
            $response->addText($value);
            $response->addSpacer();


            $label = $this->translator->translateString(translationId: 'current_speed', module: 'network_usage');
            $value = $networkStatistic->getTotalSpeedFromReferencePointReadable();
            $response->addText($label);
            $response->addText($value);
            $response->addSpacer();
        } else {
            $response->addText(':(');
        }

        $response->setRefreshInSeconds(15);
        return $response->toResponse();
    }
}
