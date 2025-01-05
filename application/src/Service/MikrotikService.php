<?php

declare(strict_types=1);

namespace App\Service;

use App\Message\AsyncJob;
use App\Service\NetworkUsageProviderSettings;
use RouterOS\Client;
use RouterOS\Query;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

final class MikrotikService
{
    private const POWER_CYCLE_STEP_DISABLE_LTE = 'disable_lte';
    private const POWER_CYCLE_STEP_ENABLE_LTE = 'enable_lte';
    private const POWER_CYCLE_STEP_INIT_POWER_CYCLE = 'init';
    private const POWER_CYCLE_STEP_REBOOT = 'reboot';

    private const POWER_CYCLE_SUCCESSOR_STATES = [
        self::POWER_CYCLE_STEP_INIT_POWER_CYCLE => self::POWER_CYCLE_STEP_DISABLE_LTE,
        self::POWER_CYCLE_STEP_DISABLE_LTE => self::POWER_CYCLE_STEP_REBOOT,
        self::POWER_CYCLE_STEP_REBOOT => self::POWER_CYCLE_STEP_ENABLE_LTE,
        self::POWER_CYCLE_STEP_ENABLE_LTE => null,
    ];

    private const POWER_CYCLE_DELAYS = [
        self::POWER_CYCLE_STEP_DISABLE_LTE => 2,
        self::POWER_CYCLE_STEP_ENABLE_LTE => 1,
        self::POWER_CYCLE_STEP_INIT_POWER_CYCLE => 1,
        self::POWER_CYCLE_STEP_REBOOT => 30,
    ];

    private ?Client $client = null;

    public function __construct(
        private MessageBusInterface $bus,
        private NetworkUsageProviderSettings $networkUsageProviderSettings,
    ) {
    }

    /*
    * Due to existing bug in Mikrotik LTE devices,
    * when router thinks there is no SIM card, these steps must be executed:
    *
    * - Disable LTE interfaces
    * - Reboot router
    * - Enable LTE interfaces
    */
    public function powerCycleMikrotik(): void
    {
        $this->handlePowerCycle(currentStep: self::POWER_CYCLE_STEP_INIT_POWER_CYCLE);
    }

    public function handlePowerCycle(string $currentStep): void
    {
        $stepAccomplished = false;
        $nextStep = self::POWER_CYCLE_SUCCESSOR_STATES[$currentStep];
        $nextStepDelay = self::POWER_CYCLE_DELAYS[$currentStep];

        switch ($currentStep) {
            case self::POWER_CYCLE_STEP_INIT_POWER_CYCLE:
                $stepAccomplished = true;
                break;
            case self::POWER_CYCLE_STEP_DISABLE_LTE:
                $stepAccomplished = $this->disableMikrotikLteInterfaces();
                break;
            case self::POWER_CYCLE_STEP_REBOOT:
                $stepAccomplished = $this->resetMikrotik();
                break;
            case self::POWER_CYCLE_STEP_ENABLE_LTE:
                $stepAccomplished = $this->enableMikrotikLteInterfaces();
                break;
        }

        if (true === $stepAccomplished) {
            if (null === $nextStep) {
                return;
            }
            $this->queuePowerCycleStep(stepName: $nextStep, stepDelaySeconds: $nextStepDelay);
        } else {
            $this->queuePowerCycleStep(stepName: $currentStep, stepDelaySeconds: 3);
        }
    }

    public function getLteStatistics(string $interfaceId)
    {
        $query = (new Query('/interface/lte/monitor'))
            ->equal('.id', $interfaceId)
            ->equal('once', 'true');
        return $this->getClient()->query($query)->read();
    }

    public function getInterfaces(): array
    {
        $query = (new Query('/interface/print'));
        return $this->getClient()->query($query)->read();
    }

    private function enableMikrotikLteInterfaces(): bool
    {
        $success = $this->toggleMikrotikLteInterfacesStatus(enabled: true);
        return $success;
    }

    private function disableMikrotikLteInterfaces(): bool
    {
        $success = $this->toggleMikrotikLteInterfacesStatus(enabled: false);
        return $success;
    }

    private function queuePowerCycleStep(string $stepName, int $stepDelaySeconds): void
    {
        $delayStamp = new DelayStamp(1000 * $stepDelaySeconds);
        $message = new AsyncJob(
            jobType: AsyncJob::TYPE_POWER_CYCLE_MIKROTIK_LTE,
            payload: [
                'step' => $stepName
            ]
        );

        $this->bus->dispatch(
            message: $message,
            stamps: [
                $delayStamp,
            ]
        );
    }

    private function resetMikrotik(): bool
    {
        $query = (new Query('/system/reboot'));
        try {
            $this->getClient()->query($query)->read();
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    private function getClient(): Client
    {
        if (!($this->client instanceof Client)) {
            $this->client = new Client([
                'host' => strval($this->networkUsageProviderSettings->getAddress()),
                'user' => 'admin',
                'pass' => strval($this->networkUsageProviderSettings->getPassword()),
                'ssl' => true,
            ]);
        }

        return $this->client;
    }

    private function toggleMikrotikLteInterfacesStatus(bool $enabled): bool
    {
        $targetDisabledStatus = (true === $enabled) ? 'false' : 'true';
        $targetApiMethod = (true === $enabled) ? 'enable' : 'disable';

        try {
            foreach ($this->getInterfaces() as $interfaceInfo) {
                if ($interfaceInfo['type'] === 'lte') {
                    if (!($interfaceInfo['disabled'] === $targetDisabledStatus)) {
                        $interfaceId = $interfaceInfo['.id'];
                        $query = (new Query('/interface/' . $targetApiMethod))
                            ->equal('.id', $interfaceId);
                        $this->getClient()->query($query)->read();
                    }
                }
            }
        } catch (\Exception) {
            return false;
        }
        return true;
    }
}
