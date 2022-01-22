<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepository;

final class TunnelInfoProvider
{
    public function __construct(
        private MailerService $mailerService,
        private SimpleCacheService $simpleCacheService,
        private UserRepository $userRepository,
    ) {
    }

    private const CACHE_KEY = 'NGROK_ADDRESS';

    public function getCurrentTunnel(): string
    {
        $tunnel = strval(file_get_contents('/ngrok/ngrok.log'));
        $tunnel = str_replace(search: '"', replace: '', subject: $tunnel);
        $tunnel = str_replace(search: "\n", replace: '', subject: $tunnel);
        $tunnel = str_replace(search: 'http://', replace: 'https://', subject: $tunnel);
        return $tunnel;
    }

    public function reactOnChanges()
    {
        $oldTunnelAddress = $this->simpleCacheService->retrieveDataFromCache(
            key: self::CACHE_KEY,
        );

        if (array_key_exists(key: self::CACHE_KEY, array: $oldTunnelAddress)) {
            $oldTunnelAddress = $oldTunnelAddress[self::CACHE_KEY];
        }

        $newTunnelAddress = $this->getCurrentTunnel();
        if ((strlen($newTunnelAddress) > 1) && false === ($oldTunnelAddress === $newTunnelAddress)) {
            $users = $this->userRepository->findAll();
            foreach ($users as $user) {
                $email = $user->getEmail();
                if (strlen($email) > 1) {
                    $this->mailerService->sendMailTunnelChange(
                        to: $email,
                        newAddress: $newTunnelAddress,
                    );
                }
            }
        }

        $cacheLength = new \DateTime('+1 month');
        $this->simpleCacheService->cacheData(
            key: self::CACHE_KEY,
            data: [
                self::CACHE_KEY => $newTunnelAddress,
            ],
            validTo: $cacheLength,
        );
    }
}
