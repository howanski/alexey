<?php

declare(strict_types=1);

namespace App\Model;

use DateTime;

final class MobileSignalInfo
{
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
}
