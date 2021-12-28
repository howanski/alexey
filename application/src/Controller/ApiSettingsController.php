<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\ApiDeviceRepository;
use App\Service\MobileApiManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mobile/access')]
final class ApiSettingsController extends AbstractController
{
    #[Route('/settings', name: 'api_local_settings')]
    public function index(ApiDeviceRepository $repo): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('api/index.html.twig', [
            'devices' => $repo->getMyDevices($user),
        ]);
    }

    #[Route('/qr.png', name: 'api_show_qr')]
    public function qr(MobileApiManager $apiManager)
    {
        $credentials = $apiManager->getFullConnectionCredentials($this->getUser());
        $fileContent = $apiManager->getInMemoryQr(
            data: $credentials,
        );
        $response = new Response();
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Content-type', 'image/png');
        $response->headers->set('Content-length', strval(strlen($fileContent)));
        $response->sendHeaders();
        $response->setContent($fileContent);

        return $response;
    }
}
