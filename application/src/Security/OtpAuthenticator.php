<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\EventSubscriber\UserLocaleSubscriber;
use App\Service\OtpManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

final class OtpAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private RouterInterface $router,
        private OtpManager $otpManager,
        private CsrfTokenManagerInterface $csrfManager,
    ) {
    }

    public function supports(Request $request): bool
    {
        return $request->isMethod('POST')
            && $this->router->generate('otp_login') === $request->getPathInfo()
            && false === ($request->request->get('otp', 'invalid_otp') === 'invalid_otp');
    }

    public function authenticate(Request $request): Passport
    {
        $otp = $request->request->get('otp', 'invalid_otp');
        $csrf = $request->request->get('_csrf_token', 'invalid_csrf');
        $token = new CsrfToken(
            id: 'authenticate_otp',
            value: $csrf,
        );
        if (false === $this->csrfManager->isTokenValid($token)) {
            $this->denyApiAccess();
        }
        $user = $this->otpManager->getUserByOtp($otp);
        if ($user instanceof User) {
            $noMoreChecksNeeded = function ($credentials, $user) {
                return true;
            };
            $username = $user->getUserIdentifier();
            $badge = new UserBadge(userIdentifier: $username);
            $credentials = new CustomCredentials($noMoreChecksNeeded, $user);
            return new Passport(userBadge: $badge, credentials: $credentials);
        }
        $this->denyApiAccess();
    }

    private function denyApiAccess(string $message = 'Access denied.', int $statusCode = 401): void
    {
        throw new CustomUserMessageAuthenticationException(message: $message, code: $statusCode);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /** @var User $user */
        $user = $token->getUser();
        $request->setLocale($user->getLocale());
        $session = $request->getSession();
        $session->set(UserLocaleSubscriber::USER_LOCALE, $user->getLocale());
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $this->otpManager->scrambleAllOTPs();
        return new RedirectResponse($this->router->generate('otp_login'));
    }
}
