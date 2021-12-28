<?php

declare(strict_types=1);

namespace App\Service;

final class TunnelInfoProvider
{
    public function getCurrentTunnel(): string
    {
        $tunnel = strval(file_get_contents('/ngrok/ngrok.log'));
        $tunnel = str_replace(search: '"', replace: '', subject: $tunnel);
        $tunnel = str_replace(search: "\n", replace: '', subject: $tunnel);
        $tunnel = str_replace(search: 'http://', replace: 'https://', subject: $tunnel);
        return $tunnel;
    }
}
