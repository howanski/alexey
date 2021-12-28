<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Controller\ApiController;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class TunnelRequestSubscriber implements EventSubscriberInterface
{
    private const ALLOWED_CONTROLLER = ApiController::class;

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $host = $request->getHost();
        if (is_int(strpos(haystack: $host, needle: '.ngrok.io'))) {
            $controller = $request->get('_controller');
            if (strpos(haystack: $controller, needle: self::ALLOWED_CONTROLLER) === false) {
                $response = new JsonResponse(
                    data: 'Tunneling forbidden',
                    status: Response::HTTP_FORBIDDEN,
                );
                $event->setResponse($response);
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 0],
            ],
        ];
    }
}
