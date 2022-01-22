<?php

declare(strict_types=1);

namespace App\Service;

use App\Class\ApiResponse;
use App\Entity\ApiDevice;
use App\Entity\NetworkMachine;
use App\Entity\NetworkStatistic;
use App\Entity\User;
use App\Message\AsyncJob;
use App\Model\SystemSettings;
use App\Model\TransmissionSettings;
use App\Repository\NetworkMachineRepository;
use App\Repository\NetworkStatisticRepository;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;

// TODO: Yeah, that's really bloated as I want features right away when I think of them
// so when the "time comes" it has to be cleared up, not mentioning about tests...
final class MobileApi
{
    private ApiDevice $currentDevice;

    public function __construct(
        private AlexeyTranslator $translator,
        private MessageBusInterface $bus,
        private NetworkMachineRepository $networkMachineRepository,
        private NetworkStatisticRepository $networkStatisticRepository,
        private OtpManager $otpManager,
        private RouterInterface $router,
        private SimpleSettingsService $simpleSettingsService,
        private TunnelInfoProvider $tunnelInfoProvider,
        private WeatherService $weatherService,
    ) {
    }

    private const API_FUNCTION_MACHINE_WAKE = 'machineWake';
    private const API_FUNCTION_MACHINES = 'machines';
    private const API_FUNCTION_NETWORK_USAGE = 'networkUsage';
    private const API_FUNCTION_WEATHER = 'weather';
    private const API_FUNCTION_TUNNEL = 'tunnel';
    public const API_FUNCTION_DASHBOARD = 'dashboard';

    private const API_FUNCTIONS = [
        self::API_FUNCTION_DASHBOARD => 'getDashboard',
        self::API_FUNCTION_MACHINE_WAKE => 'wakeMachine',
        self::API_FUNCTION_MACHINES => 'getMachines',
        self::API_FUNCTION_NETWORK_USAGE => 'getNetworkUsage',
        self::API_FUNCTION_TUNNEL => 'manageTunnel',
        self::API_FUNCTION_WEATHER => 'getWeather',
    ];

    public const API_PERMISSIONS = [
        self::API_FUNCTION_MACHINES,
        self::API_FUNCTION_NETWORK_USAGE,
        self::API_FUNCTION_TUNNEL,
        self::API_FUNCTION_WEATHER,
    ];

    public function processFunction(
        User $user,
        ApiDevice $currentDevice,
        string $functionName,
        array $parameters = [],
    ): JsonResponse {
        $this->currentDevice = $currentDevice;
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
            $errorResponse = new ApiResponse($user->getLocale());
            $errorResponse->setCode(500);
            $errorResponse->setMessage($e->getMessage());
            return $errorResponse->toResponse();
        }
    }

    private function canRun(string $functionName): bool
    {
        return $this->currentDevice->hasPermission($functionName);
    }

    private function apiFunctionPath(string $function, $parameters = []): string
    {
        $allParams = [
            'function' => $function,
        ];

        foreach ($parameters as $paramName => $paramValue) {
            $allParams[$paramName] = $paramValue;
        }

        return $this->router->generate(
            name: 'api',
            parameters: $allParams,
        );
    }

    private function getDashboard(User $user, array $parameters): JsonResponse
    {
        $response = new ApiResponse($user->getLocale());
        $response->addText(
            $this->translator->translateString(
                translationId: 'hi',
                module: 'common'
            ) .
                ', ' . $user->getUserIdentifier() . ' !'
        );
        $response->addSpacer();

        $hasAnyPermission = false;

        if ($this->canRun(self::API_FUNCTION_MACHINES)) {
            $hasAnyPermission = true;
            $response->addButton(
                name: $this->translator->translateString(
                    translationId: 'menu_record',
                    module: 'network_machines'
                ),
                path: $this->apiFunctionPath(self::API_FUNCTION_MACHINES),
            );
        }

        if ($this->canRun(self::API_FUNCTION_WEATHER)) {
            $hasAnyPermission = true;
            $response->addButton(
                name: $this->translator->translateString(
                    translationId: 'menu_record',
                    module: 'weather'
                ),
                path: $this->apiFunctionPath(self::API_FUNCTION_WEATHER),
            );
        }

        if ($this->canRun(self::API_FUNCTION_NETWORK_USAGE)) {
            $hasAnyPermission = true;
            $response->addButton(
                name: $this->translator->translateString(
                    translationId: 'menu_record',
                    module: 'network_usage'
                ),
                path: $this->apiFunctionPath(self::API_FUNCTION_NETWORK_USAGE),
            );
        }

        if ($this->canRun(self::API_FUNCTION_TUNNEL)) {
            $hasAnyPermission = true;
            $response->addButton(
                name: $this->translator->translateString(
                    translationId: 'manage_tunnel',
                    module: 'settings'
                ),
                path: $this->apiFunctionPath(self::API_FUNCTION_TUNNEL),
            );
        }

        if (false === $hasAnyPermission) {
            $response->addText($this->translator->translateString('setup_permissions', 'api'));
            $response->addSpacer();
        }

        return $response->toResponse();
    }

    private function getMachines(User $user, array $parameters): JsonResponse
    {
        if (false === $this->canRun(self::API_FUNCTION_MACHINES)) {
            return $this->getDashboard($user, $parameters);
        }
        $response = new ApiResponse($user->getLocale());
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
                    path: $this->apiFunctionPath(self::API_FUNCTION_MACHINE_WAKE, [
                        'id' => $machine->getId(),
                    ]),
                );
            }
            $response->addSpacer();
        }
        $response->setRefreshInSeconds(15);

        return $response->toResponse();
    }

    private function wakeMachine(User $user, array $parameters): JsonResponse
    {
        if (false === $this->canRun(self::API_FUNCTION_MACHINES)) {
            return $this->getDashboard($user, $parameters);
        }
        $response = new ApiResponse($user->getLocale());

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
        $response->addSpacer();
        $response->addButton(
            name: '<- ' . $this->translator->translateString(
                translationId: 'back',
                module: 'common'
            ),
            path: $this->apiFunctionPath(self::API_FUNCTION_MACHINES),
        );
        $response->addSpacer();

        return $response->toResponse();
    }

    private function getWeather(User $user, array $parameters): JsonResponse
    {
        if (false === $this->canRun(self::API_FUNCTION_WEATHER)) {
            return $this->getDashboard($user, $parameters);
        }
        $response = new ApiResponse($user->getLocale());

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
        if (false === $this->canRun(self::API_FUNCTION_NETWORK_USAGE)) {
            return $this->getDashboard($user, $parameters);
        }
        $response = new ApiResponse($user->getLocale());
        $response->addSpacer();
        $transmissionSettings = new TransmissionSettings();
        $transmissionSettings->selfConfigure($this->simpleSettingsService);

        $networkStatistic = $this->networkStatisticRepository->getLatestOne();
        if ($networkStatistic instanceof NetworkStatistic) {
            $label = $this->translator->translateString(translationId: 'optimal_speed', module: 'network_usage');
            $value = $networkStatistic->getTransferRateLeftReadable(
                precision: 3,
                frameWidth: TransmissionSettings::TARGET_SPEED_FRAME_FULL
            );
            $value .= ' | ';
            $value .= $networkStatistic->getTransferRateLeftReadable(
                precision: 3,
                frameWidth: TransmissionSettings::TARGET_SPEED_FRAME_DAY
            );
            $response->addText($label);
            $response->addText($value);
            $response->addSpacer();


            $label = $this->translator->translateString(translationId: 'traffic_left', module: 'network_usage');
            $value = $networkStatistic->getTrafficLeftReadable(
                precision: 3,
                frameWidth: TransmissionSettings::TARGET_SPEED_FRAME_FULL,
            );
            $value .= ' | ';
            $value .= $networkStatistic->getTrafficLeftReadable(
                precision: 3,
                frameWidth: TransmissionSettings::TARGET_SPEED_FRAME_DAY,
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

    private function manageTunnel(User $user, array $parameters): JsonResponse
    {
        if (false === $this->canRun(self::API_FUNCTION_TUNNEL)) {
            return $this->getDashboard($user, $parameters);
        }
        $response = new ApiResponse($user->getLocale());
        $response->addSpacer();
        $response->addText($this->tunnelInfoProvider->getCurrentTunnel());
        $response->addSpacer();

        //TODO: some sort of voter if user can change this setting
        if (array_key_exists(key: 'allow', array: $parameters)) {
            $newSetting = $parameters['allow'];
            $this->simpleSettingsService->saveSettings(
                [
                    SystemSettings::TUNNELING_ALLOWED => $newSetting,
                ],
                null
            );
        }

        $tunnelSetup = $this->simpleSettingsService->getSettings([SystemSettings::TUNNELING_ALLOWED], null);
        $tunnelSetup = $tunnelSetup[SystemSettings::TUNNELING_ALLOWED];
        $tunnelAllowed = ($tunnelSetup === SimpleSettingsService::UNIVERSAL_TRUTH);
        if (true === $tunnelAllowed) {
            $response->addButton(
                name: $this->translator->translateString(
                    translationId: 'turn_off',
                    module: 'common'
                ),
                path: $this->apiFunctionPath(self::API_FUNCTION_TUNNEL, [
                    'allow' => SimpleSettingsService::UNIVERSAL_FALSE,
                ]),
            );
            $response->addSpacer();
            $otp = $this->otpManager->getNewOtp($user);
            $response->addText('OTP: ' . $otp);
            $response->addLink(
                name: $this->translator->translateString(
                    translationId: 'open_in_browser',
                    module: 'api'
                ),
                path: $this->tunnelInfoProvider->getCurrentTunnel() .
                    $this->router->generate(name: 'otp_login', parameters: [
                        'otp' => $otp,
                    ]),
            );
        } else {
            $response->addButton(
                name: $this->translator->translateString(
                    translationId: 'turn_on',
                    module: 'common'
                ),
                path: $this->apiFunctionPath(self::API_FUNCTION_TUNNEL, [
                    'allow' => SimpleSettingsService::UNIVERSAL_TRUTH,
                ]),
            );
        }

        $response->addSpacer();
        return $response->toResponse();
    }
}
