<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\SimpleSetting;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SimpleSettingRepository;

class SimpleSettingsService
{
    public const UNIVERSAL_TRUTH = 'BOOL_TRUE';
    public const UNIVERSAL_FALSE = 'BOOL_FALSE';

    private EntityManagerInterface $em;

    private SimpleSettingRepository $simpleSettingRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->simpleSettingRepository = $this->em->getRepository(SimpleSetting::class);
    }

    public function getSettings(array $settingsKeys): array
    {
        $result = [];
        foreach ($settingsKeys as $key) {
            $result[$key] = '';
        }
        /**
         * @var SimpleSetting $simpleSetting
         */
        foreach ($this->simpleSettingRepository->findAllByKeys($settingsKeys) as $simpleSetting) {
            $result[$simpleSetting->getSettingKey()] = $simpleSetting->getSettingValue();
        }
        return $result;
    }

    public function saveSettings(array $settings)
    {
        foreach ($settings as $key => $value) {
            $entity = $this->simpleSettingRepository->findOneBy(['settingKey' => $key]);
            if (!($entity instanceof SimpleSetting)) {
                $entity = new SimpleSetting();
                $entity->setSettingKey($key);
            }
            $entity->setSettingValue($value);
            $this->em->persist($entity);
        }
        $this->em->flush();
    }
}
