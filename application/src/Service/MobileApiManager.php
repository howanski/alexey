<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ApiDevice;
use App\Entity\User;
use App\Repository\ApiDeviceRepository;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Symfony\Component\Routing\RouterInterface;

final class MobileApiManager
{
    public function __construct(
        private ApiDeviceRepository $deviceRepository,
        private RouterInterface $router,
        private TunnelInfoProvider $tunnel,
        private string $salt,
    ) {
    }

    private function generateQrResult(string $data): ResultInterface
    {
        $writer = new PngWriter();
        $qrCode = QrCode::create($data)
            ->setEncoding(new Encoding('ISO-8859-1'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize(300)
            ->setMargin(0)
            ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->setForegroundColor(new Color(218, 223, 234))
            ->setBackgroundColor(new Color(76, 85, 107, 0));

        $result = $writer->write($qrCode);
        return $result;
    }

    public function getInMemoryQr(string $data): string
    {
        return $this->generateQrResult($data)->getString();
    }

    public function generateUserToken(User $user)
    {
        $whirpoolOne =
            $user->getId() .
            $user->getEmail() .
            intval(time() / 86400) .
            strval($this->deviceRepository->countMyDevices($user));

        $whirpoolTwo = '';
        /** @var ApiDevice $device */
        foreach ($this->deviceRepository->getMyDevices($user) as $device) {
            $whirpoolTwo .= $device->getSecret();
            $whirpoolTwo .= $device->getName();
        }

        return hash_hmac(
            algo: 'sha256',
            data: $whirpoolOne,
            key: $this->salt . $whirpoolTwo,
        );
    }

    public function getFullConnectionCredentials(User $user): string
    {
        $connectionData = [
            'server' => $this->tunnel->getCurrentTunnel(),
            'token' => $this->generateUserToken($user),
            'path' => $this->router->generate(
                name: 'api',
                parameters: [
                    'function' => MobileApi::API_FUNCTION_DASHBOARD,
                ]
            ),
        ];
        return json_encode($connectionData);
    }
}
