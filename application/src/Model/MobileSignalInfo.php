<?php

declare(strict_types=1);

namespace App\Model;

use App\Class\GaussianGauge;
use App\Service\SimpleCacheService;
use DateTime;
use Symfony\Component\HttpFoundation\Response;

final class MobileSignalInfo
{
    private const CACHE_KEY = 'MOBILE_SIGNAL';

    public DateTime $fetchedAt;
    public float $rsrq = 0; //dB
    public float $sinr = 0; //dB
    public string $band = '0';
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
            $this->band = (string) $cachedData['band'];
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

    public function getAjaxGaugeData(string $gauge): Response
    {
        #https://www.speedcheck.org/pl/wiki/rssi/
        #https://i0.wp.com/www.cablefree.net/wp-content/uploads/2016/04/LTE-RF-Conditions.png
        $config = [
            'rssi' => [
                'value' => $this->rssi,
                'optimum' => -30,
                'greenZoneWidth' => 37,
                'yellowZoneWidth' => 23,
            ],
            'rsrq' => [
                'value' => $this->rsrq,
                'optimum' => -5,
                'greenZoneWidth' => 10,
                'yellowZoneWidth' => 5,
            ],
            'rsrp' => [
                'value' => $this->rsrp,
                'optimum' => -70,
                'greenZoneWidth' => 20,
                'yellowZoneWidth' => 10,
            ],
            'sinr' => [
                'value' => $this->sinr,
                'optimum' => 20,
                'greenZoneWidth' => 7,
                'yellowZoneWidth' => 13,
            ],
            'signal' => [
                'value' => $this->signalStrengthPercent,
                'optimum' => 100,
                'greenZoneWidth' => 30,
                'yellowZoneWidth' => 20,
            ],
        ];

        $gauge = new GaussianGauge(
            value: $config[$gauge]['value'],
            optimum: $config[$gauge]['optimum'],
            greenZoneWidth: $config[$gauge]['greenZoneWidth'],
            yellowZoneWidth: $config[$gauge]['yellowZoneWidth'],
            leftHalf: true,
        );

        $gauge->setBonusPayload(
            [
                'rsrq' => $this->rsrq . ' dB',
                'sinr' => $this->sinr . ' dB',
                'band' => $this->band,
                'cellId' => $this->cellId,
                'pci' => $this->pci,
                'plmn' => $this->plmn,
                'rsrp' => $this->rsrp . ' dBm',
                'rssi' => $this->rssi . ' dBm',
                'signalStrengthPercent' => $this->signalStrengthPercent . ' %',
                'txpower' => $this->txpower,
            ]
        );

        return $gauge->getXmlResponse();
    }
}
