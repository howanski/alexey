<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\ApiDevice;
use App\Repository\ApiDeviceRepository;
use App\Repository\UserRepository;
use App\Service\MobileApiManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

final class ApiAuthenticator extends AbstractAuthenticator
{
    private const SECRET_HEADER = 'X-ALEXEY-SECRET';
    private const CORS_PRE_FLIGHT = 'Access-Control-Request-Headers';
    private const CORS_HEADERS = [
        'Access-Control-Allow-Origin' => '*',
    ];
    private const CORS_HEADERS_FULL = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Headers' => self::SECRET_HEADER,
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private ApiDeviceRepository $deviceRepository,
        private UserRepository $userRepository,
        private MobileApiManager $apiManager,
    ) {
    }

    public function supports(Request $request): bool
    {
        return true;
    }

    public function authenticate(Request $request): Passport
    {
        $secret = $request->headers->get(key: self::SECRET_HEADER, default: 'NOT_PROVIDED_ANY_SECRET');
        $apiDevice = $this->deviceRepository->findOneBy(criteria: ['secret' => $secret]);
        $now = new DateTime('now');
        $noMoreChecksNeeded = function ($credentials, $user) {
            return true;
        };
        if ($apiDevice instanceof ApiDevice) {
            try {
                $apiDevice->setLastRequest($now);
                $this->em->persist($apiDevice);
                $this->em->flush();

                $user = $apiDevice->getUser();
                $username = $user->getUserIdentifier();
                $badge = new UserBadge(userIdentifier: $username);
                $credentials = new CustomCredentials($noMoreChecksNeeded, $user);
                return new Passport(userBadge: $badge, credentials: $credentials);
            } catch (Exception) {
                $this->denyApiAccess();
            }
        } else {
            foreach ($this->userRepository->findAll() as $user) {
                $userToken = $this->apiManager->generateUserToken($user);
                if (hash_equals($userToken, $secret)) {
                    $apiDevice = new ApiDevice();
                    $apiDevice->setSecret($secret);
                    $apiDevice->setLastRequest($now);
                    $apiDevice->setUser($user);
                    $apiDevice->resetPermissions();
                    $apiDevice->setName('--- ' . $now->format('Y-m-d H:i:s') . ' ---');
                    $this->em->persist($apiDevice);
                    $this->em->flush();

                    $username = $user->getUserIdentifier();
                    $badge = new UserBadge(userIdentifier: $username);
                    $credentials = new CustomCredentials($noMoreChecksNeeded, $user);
                    return new Passport(userBadge: $badge, credentials: $credentials);
                }
            }
            if (
                $request->headers->has(self::CORS_PRE_FLIGHT)
                && strtolower(self::SECRET_HEADER) === strtolower($request->headers->get(self::CORS_PRE_FLIGHT))
            ) {
                $this->denyApiAccess(message: 'Shut up, preflight!', statusCode: 200);
            }
            $this->denyApiAccess();
        }
    }

    private function denyApiAccess(string $message = 'Access denied.', int $statusCode = 401)
    {
        throw new CustomUserMessageAuthenticationException(message: $message, code: $statusCode);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $code = $exception->getCode();
        $data = [
            'message' => strval($exception->getMessage()),
            'code' => $code,
        ];

        if (200 === $code) {
            $headers = self::CORS_HEADERS_FULL;
        } else {
            $headers = self::CORS_HEADERS;
        }

        return new JsonResponse(
            data: $data,
            status: $code,
            headers: $headers,
        );
    }
}
