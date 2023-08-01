<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Controller\ApiController;
use App\Model\SystemSettings;
use App\Service\SimpleSettingsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class TunnelRequestSubscriber implements EventSubscriberInterface
{
    private const ALLOWED_CONTROLLER = ApiController::class;

    public function __construct(
        private SimpleSettingsService $settings,
    ) {
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $host = $request->getHost();
        if (is_int(strpos(haystack: $host, needle: 'ngrok'))) {
            $controller = $request->get('_controller');
            if (strpos(haystack: $controller, needle: self::ALLOWED_CONTROLLER) === false) {
                if ($this->isTunnellingAllowed()) {
                    return;
                }
                $response = new JsonResponse(
                    data: 'Tunneling forbidden',
                    status: Response::HTTP_FORBIDDEN,
                );
                $event->setResponse($response);
            }
        }
    }

    private function isTunnellingAllowed(): bool
    {
        $setting = $this->settings->getSettings([SystemSettings::TUNNELING_ALLOWED], null);
        $setting = $setting[SystemSettings::TUNNELING_ALLOWED];
        return $setting === SimpleSettingsService::UNIVERSAL_TRUTH;
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
