<?php

declare(strict_types=1);

namespace App\Model;

use App\Service\SimpleCacheService;
use DateTime;

final class MobileSignalInfo
{
    private const CACHE_KEY = 'MOBILE_SIGNAL';

    public DateTime $fetchedAt;
    public float $rsrq = 0; //dB
    public float $sinr = 0; //dB
    public int $band = 0;
    public int $cellId = 0;
    public int $pci = 0;
    public int $plmn = 0;
    public int $rsrp = 0; //dBm
    public int $rssi = 0; //dBm
    public int $signalStrengthPercent = 0;
    public string $error = '';
    public string $errorOn = '';
    public string $txpower = '';

    public function __construct(
        private SimpleCacheService $cache,
    ) {
        $cachedData = $cache->retrieveDataFromCache(key: self::CACHE_KEY);
        if (sizeof($cachedData) === 10) {
            $this->rsrq = $cachedData['rsrq'];
            $this->sinr = $cachedData['sinr'];
            $this->band = $cachedData['band'];
            $this->cellId = $cachedData['cellId'];
            $this->pci = $cachedData['pci'];
            $this->plmn = $cachedData['plmn'];
            $this->rsrp = $cachedData['rsrp'];
            $this->rssi = $cachedData['rssi'];
            $this->signalStrengthPercent = $cachedData['signalStrengthPercent'];
            $this->txpower = $cachedData['txpower'];
        }
    }

    public function save()
    {
        $timeValid = new DateTime('now');
        $timeValid->modify('+1 day');
        $data = [
            'rsrq' => $this->rsrq,
            'sinr' => $this->sinr,
            'band' => $this->band,
            'cellId' => $this->cellId,
            'pci' => $this->pci,
            'plmn' => $this->plmn,
            'rsrp' => $this->rsrp,
            'rssi' => $this->rssi,
            'signalStrengthPercent' => $this->signalStrengthPercent,
            'txpower' => $this->txpower,
        ];
        $this->cache->cacheData(
            key: self::CACHE_KEY,
            data: $data,
            validTo: $timeValid,
        );
    }
}
