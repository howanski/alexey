<?php

namespace App\Class;

/**
 * Howanski's Helpers
 */
class HHelpers
{
    public static function formatBytes(int $bytes, int $precision = 2, bool $asPowerOfTens = false): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');

        $bytes = max($bytes, 0);
        $power = floor(($bytes ? log($bytes) : 0) / log(1024));
        $power = min($power, count($units) - 1);

        if ($asPowerOfTens) {
            $bytes /= (1 << (10 * $power));
        } else {
            $bytes /= pow(1024, $power);
        }

        return round($bytes, $precision) . ' ' . $units[$power];
    }
}
