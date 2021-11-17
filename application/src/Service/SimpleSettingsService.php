<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Entity\SimpleSetting;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SimpleSettingRepository;

class SimpleSettingsService
{
    public const UNIVERSAL_TRUTH = 'BOOL_TRUE';
    public const UNIVERSAL_FALSE = 'BOOL_FALSE';

    public function __construct(
        private EntityManagerInterface $em,
        private SimpleSettingRepository $simpleSettingRepository
    ) {
    }

    public function getSettings(array $settingsKeys, User $user = null): array
    {
        $result = [];
        foreach ($settingsKeys as $key) {
            $result[$key] = '';
        }
        /**
         * @var SimpleSetting $simpleSetting
         */
        foreach ($this->simpleSettingRepository->findAllByKeys(keys: $settingsKeys, user: $user) as $simpleSetting) {
            $result[$simpleSetting->getSettingKey()] = $simpleSetting->getSettingValue();
        }
        return $result;
    }

    public function saveSettings(array $settings, User $user = null)
    {
        foreach ($settings as $key => $value) {
            $criteria = ['settingKey' => $key];
            if ($user instanceof User) {
                $criteria['user'] = $user;
            }
            $entity = $this->simpleSettingRepository->findOneBy($criteria);
            if (!($entity instanceof SimpleSetting)) {
                $entity = new SimpleSetting();
                $entity->setSettingKey($key);
                $entity->setUser($user);
            }
            $entity->setSettingValue($value);
            $this->em->persist($entity);
        }
        $this->em->flush();
    }
}
