<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

final class OtpManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private string $salt,
        private UserRepository $userRepository,
    ) {
    }

    public function getNewOtp(User $user): string
    {
        $newOtp = $this->getRandomHash($user);
        $user->setOtp($newOtp);
        $this->em->persist($user);
        $this->em->flush();
        return $newOtp;
    }

    public function getUserByOtp(string $otp)
    {
        if (strlen($otp) < 15) {
            return null;
        }
        $user = $this->userRepository->findOneBy([
            'otp' => $otp,
        ]);

        if ($user instanceof User) {
            // User found, otp no longer usable, scramble
            $this->getNewOtp($user);
        }

        return $user;
    }

    public function scrambleAllOtps(): void
    {
        $users = $this->userRepository->findAll();
        foreach ($users as $user) {
            $this->getNewOtp($user);
        }
    }

    private function getRandomHash(User $user)
    {
        $whirpoolOne =
            $user->getId() .
            $user->getEmail() .
            time();

        $whirpoolTwo = $user->getOtp();

        $longHash = hash_hmac(
            algo: 'sha256',
            data: $whirpoolOne,
            key: $this->salt . $whirpoolTwo,
        );

        $shortHash = substr($longHash, 3, 15);
        return $shortHash;
    }
}
