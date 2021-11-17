<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\SimpleCache;
use App\Repository\SimpleCacheRepository;
use Doctrine\ORM\EntityManagerInterface;

final class SimpleCacheService
{
    public function __construct(
        private EntityManagerInterface $em,
        private SimpleCacheRepository $simpleCacheRepository,
    ) {
    }

    public function cacheData(string $key, array $data, \DateTimeInterface $validTo): void
    {
        $entity = $this->simpleCacheRepository->findRecordByKey($key);
        if (!($entity instanceof SimpleCache)) {
            $entity = new SimpleCache();
            $entity->setCacheKey($key);
        }
        $entity->setCacheData($data);
        $entity->setValidTo($validTo);
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function retrieveDataFromCache(string $key): array
    {
        $entity = $this->simpleCacheRepository->findValidRecordByKey($key);
        if ($entity instanceof SimpleCache) {
            return $entity->getCacheData();
        }
        return [];
    }

    public function invalidateCache(string $key): void
    {
        $entity = $this->simpleCacheRepository->findValidRecordByKey($key);
        if ($entity instanceof SimpleCache) {
            $entity->setValidTo(new \DateTime('today'));
            $this->em->persist($entity);
            $this->em->flush();
        }
    }
}
