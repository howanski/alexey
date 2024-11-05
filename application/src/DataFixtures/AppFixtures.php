<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

final class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $testUser = new User();
        $testUser->setUsername('test_user');
        $testUser->setPassword('test_password');
        $manager->persist($testUser);

        $manager->flush();
    }
}
