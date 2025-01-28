<?php

declare(strict_types=1);

namespace App\Service;

use App\Message\AsyncJob;
use App\Service\NetworkUsageProviderSettings;
use DateTime;
use RouterOS\Client;
use RouterOS\Query;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

final class MikrotikService
{
    private const CACHE_KEY_POWER_CYCLE = 'mikrotik_last_power_cycle';
    private const POWER_CYCLE_STEP_DISABLE_LTE = 'disable_lte';
    private const POWER_CYCLE_STEP_ENABLE_LTE = 'enable_lte';
    private const POWER_CYCLE_STEP_INIT_POWER_CYCLE = 'init';
    private const POWER_CYCLE_STEP_REBOOT = 'reboot';
    private const POWER_CYCLE_STEP_SNIFF_SIM = 'sniff_sim';
    private const SHORT_CYCLE_STEP_INIT = 'init_short';
    private const SHORT_CYCLE_STEP_TOGGLE_LTE_DISABLE = 'short_lte_disable';
    private const SHORT_CYCLE_STEP_TOGGLE_LTE_ENABLE = 'short_lte_enable';

    private const POWER_CYCLE_SUCCESSOR_STATES = [
        self::POWER_CYCLE_STEP_DISABLE_LTE => self::POWER_CYCLE_STEP_REBOOT,
        self::POWER_CYCLE_STEP_ENABLE_LTE => null,
        self::POWER_CYCLE_STEP_INIT_POWER_CYCLE => self::POWER_CYCLE_STEP_DISABLE_LTE,
        self::POWER_CYCLE_STEP_REBOOT => self::POWER_CYCLE_STEP_ENABLE_LTE,
        self::POWER_CYCLE_STEP_SNIFF_SIM => null,
        self::SHORT_CYCLE_STEP_INIT => self::SHORT_CYCLE_STEP_TOGGLE_LTE_DISABLE,
        self::SHORT_CYCLE_STEP_TOGGLE_LTE_DISABLE => self::SHORT_CYCLE_STEP_TOGGLE_LTE_ENABLE,
        self::SHORT_CYCLE_STEP_TOGGLE_LTE_ENABLE => null,
    ];

    private const POWER_CYCLE_DELAYS = [
        self::POWER_CYCLE_STEP_DISABLE_LTE => 2,
        self::POWER_CYCLE_STEP_ENABLE_LTE => 1,
        self::POWER_CYCLE_STEP_INIT_POWER_CYCLE => 1,
        self::POWER_CYCLE_STEP_REBOOT => 30,
        self::POWER_CYCLE_STEP_SNIFF_SIM => 1,
        self::SHORT_CYCLE_STEP_INIT => 1,
        self::SHORT_CYCLE_STEP_TOGGLE_LTE_DISABLE => 5,
        self::SHORT_CYCLE_STEP_TOGGLE_LTE_ENABLE => 1,
    ];

    private ?Client $client = null;

    public function __construct(
        private MessageBusInterface $bus,
        private NetworkUsageProviderSettings $networkUsageProviderSettings,
        private SimpleCacheService $simpleCacheService,
    ) {
    }

    /*
    * Due to existing bug in Mikrotik LTE devices,
    * when router thinks there is no SIM card, these steps must be executed:
    *
    * - Disable LTE interfaces
    * - Reboot router
    * - Enable LTE interfaces
    *
    * Optionally we can disable and re-enable interfaces if it is only issue with connection
    * and SIM card state is fine
    */
    public function powerCycleMikrotik(
        bool $force = false,
        bool $shortCycle = false
    ): void {
        $canRunNow = true;
        if (false === $force) {
            // throttle runs so they are dispatched every 5 minutes at most
            $canRunNow = !$this->isPowerCycleLocked();
        }

        if (true === $canRunNow) {
            if (true === $shortCycle) {
                $this->handlePowerCycle(currentStep: self::SHORT_CYCLE_STEP_INIT);
            } else {
                $this->handlePowerCycle(currentStep: self::POWER_CYCLE_STEP_INIT_POWER_CYCLE);
            }
        }
    }

    public function handleSimCardBugIfOccurs(): void
    {
        $this->queuePowerCycleStep(
            stepName: self::POWER_CYCLE_STEP_SNIFF_SIM,
            stepDelaySeconds: 1,
        );
    }



    public function handlePowerCycle(string $currentStep): void
    {
        $stepAccomplished = false;
        $nextStep = self::POWER_CYCLE_SUCCESSOR_STATES[$currentStep];
        $nextStepDelay = self::POWER_CYCLE_DELAYS[$currentStep];

        switch ($currentStep) {
            case self::POWER_CYCLE_STEP_INIT_POWER_CYCLE:
            case self::SHORT_CYCLE_STEP_INIT:
                $stepAccomplished = true;
                break;
            case self::POWER_CYCLE_STEP_DISABLE_LTE:
            case self::SHORT_CYCLE_STEP_TOGGLE_LTE_DISABLE:
                $stepAccomplished = $this->disableMikrotikLteInterfaces();
                break;
            case self::POWER_CYCLE_STEP_REBOOT:
                $stepAccomplished = $this->resetMikrotik();
                break;
            case self::POWER_CYCLE_STEP_ENABLE_LTE:
            case self::SHORT_CYCLE_STEP_TOGGLE_LTE_ENABLE:
                $stepAccomplished = $this->enableMikrotikLteInterfaces();
                break;
            case self::POWER_CYCLE_STEP_SNIFF_SIM:
                $stepAccomplished = $this->sniffAndHandleSimCardNotFoundBug();
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

        $this->lockPowerCycle();
    }

    public function getLteStatistics(string $interfaceId): array
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
            $this->client = null;
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
        $executionSuccessful = false;

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
            $executionSuccessful = true;
            foreach ($this->getInterfaces() as $interfaceInfo) {
                if ($interfaceInfo['type'] === 'lte') {
                    if (!($interfaceInfo['disabled'] === $targetDisabledStatus)) {
                        $executionSuccessful = false;
                    }
                }
            }
        } catch (\Exception) {
            return false;
        }
        return $executionSuccessful;
    }

    private function sniffAndHandleSimCardNotFoundBug(): bool
    {
        try {
            $simCardDetectable = false;
            $connectionDown = false;
            foreach ($this->getInterfaces() as $interfaceInfo) {
                if ($interfaceInfo['type'] === 'lte') {
                    if (
                        isset($interfaceInfo['last-link-down-time'])
                        && strlen($interfaceInfo['last-link-down-time']) > 18
                    ) {
                        $lastUptime = new DateTime($interfaceInfo['last-link-up-time']);
                        $lastDownTime = new DateTime($interfaceInfo['last-link-down-time']);
                        if ($lastDownTime > $lastUptime) {
                            $connectionDown = true;
                        }
                    }
                    $lteData = $this->getLteStatistics(interfaceId: $interfaceInfo['.id']);
                    if (isset($lteData[0]['imsi'])) {
                        $imsi = $lteData[0]['imsi'];
                        if (is_string($imsi) && strlen($imsi) > 0) {
                            $simCardDetectable = true;
                        }
                    }
                }
            }
            if (false === $simCardDetectable) {
                $this->powerCycleMikrotik(force: false, shortCycle: false);
            } elseif (true === $connectionDown) {
                $this->powerCycleMikrotik(force: false, shortCycle: true);
            }
        } catch (\Exception) {
            return false;
        }

        return true;
    }

    private function isPowerCycleLocked(): bool
    {
        $cachedInfo = $this->simpleCacheService->retrieveDataFromCache(self::CACHE_KEY_POWER_CYCLE);
        if (array_key_exists(key: self::CACHE_KEY_POWER_CYCLE, array: $cachedInfo)) {
            return true;
        }
        return false;
    }

    private function lockPowerCycle(): void
    {
        $now = new \DateTime('now');
        $validTo = new \DateTime('+5 minutes');
        $this->simpleCacheService->cacheData(
            key: self::CACHE_KEY_POWER_CYCLE,
            data: [
                self::CACHE_KEY_POWER_CYCLE => $now,
            ],
            validTo: $validTo,
        );
    }
}
