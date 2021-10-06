<?php

declare(strict_types=1);

namespace App\Class;

// Howanski's Helpers
final class HHelpers
{
    public static function formatBytes(int|float $bytes, int $precision = 2, bool $asPowerOfTens = false): string
    {
        $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');

        $bytes = max($bytes, 0);
        $power = floor((($bytes > 0) ? log($bytes) : 0) / log(1024));
        $power = min($power, count($units) - 1);

        if (true === $asPowerOfTens) {
            $bytes /= (1 << (10 * $power));
        } else {
            $bytes /= pow(1024, $power);
        }

        return round($bytes, $precision) . ' ' . $units[$power];
    }
}
