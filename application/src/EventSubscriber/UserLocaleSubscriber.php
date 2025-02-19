<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

final class UserLocaleSubscriber implements EventSubscriberInterface
{
    public const USER_LOCALE = 'alexey_user_locale';

    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $userLocale = $this->requestStack->getSession()->get(self::USER_LOCALE);
        if (is_string($userLocale) && strlen($userLocale) > 0) {
            $request->setLocale($userLocale);
        }
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        /**
         * @var User $user
         */
        $user = $event->getAuthenticationToken()->getUser();
        $userLocale = $user->getLocale();
        $session = $this->requestStack->getSession();
        $session->set(self::USER_LOCALE, $userLocale);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 17], //read from session
            ],
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin', //store in session
        ];
    }
}
