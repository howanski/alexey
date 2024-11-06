<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ApiDevice;
use App\Form\ApiDeviceType;
use App\Repository\ApiDeviceRepository;
use App\Service\AlexeyTranslator;
use App\Service\MobileApiManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/mobile/access')]
final class ApiSettingsController extends AlexeyAbstractController
{
    #[Route('/settings', name: 'api_local_settings')]
    public function index(ApiDeviceRepository $repo, MobileApiManager $manager): Response
    {
        $user = $this->alexeyUser();
        $token = $manager->generateUserToken($user);

        return $this->render('api/index.html.twig', [
            'devices' => $repo->getMyDevices($user),
            'token' => $token,
            'token_check_url' => $this->generateUrl('api_my_token'),
        ]);
    }

    #[Route('/qr.png', name: 'api_show_qr')]
    public function qr(MobileApiManager $apiManager)
    {
        $user = $this->alexeyUser();
        $credentials = $apiManager->getFullConnectionCredentials($user);
        $fileContent = $apiManager->getInMemoryQr(
            data: $credentials,
        );
        $response = new Response();
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Content-type', 'image/png');
        $response->headers->set('Content-length', strval(strlen($fileContent)));
        // $response->sendHeaders();
        $response->setContent($fileContent);

        return $response;
    }

    #[Route('/token', name: 'api_my_token')]
    public function myToken(MobileApiManager $manager)
    {
        $user = $this->alexeyUser();
        $token = $manager->generateUserToken($user);
        return new JsonResponse(data: $token);
    }

    #[Route('/device/edit/{id}', name: 'api_device_edit')]
    public function editDevice(
        AlexeyTranslator $translator,
        int $id,
        Request $request,
    ): Response {
        $apiDevice = $this->fetchEntityById(className: ApiDevice::class, id: $id);
        $user = $this->alexeyUser();
        if ($apiDevice->getUser() === $user) {
            $form = $this->createForm(ApiDeviceType::class, $apiDevice);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->em->persist($apiDevice);
                $this->em->flush();
                $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));

                return $this->redirectToRoute('api_local_settings', [], Response::HTTP_SEE_OTHER);
            }

            return $this->renderForm('api/edit.html.twig', [
                'form' => $form,
            ]);
        } else {
            return $this->redirectToRoute('api_local_settings');
        }
    }

    #[Route('/device/drop/{id}', name: 'api_device_drop')]
    public function dropDevice(int $id)
    {
        $apiDevice = $this->fetchEntityById(className: ApiDevice::class, id: $id);
        $user = $this->alexeyUser();
        if ($apiDevice->getUser() === $user) {
            $this->em->remove($apiDevice);
            $this->em->flush();
        }

        return $this->redirectToRoute('api_local_settings');
    }
}
