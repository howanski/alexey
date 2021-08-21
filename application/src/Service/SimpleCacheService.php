<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\SimpleCache;
use App\Repository\SimpleCacheRepository;
use Doctrine\ORM\EntityManagerInterface;

class SimpleCacheService
{
    private EntityManagerInterface $em;

    private SimpleCacheRepository $simpleCacheRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->simpleCacheRepository = $em->getRepository(SimpleCache::class);
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
        $this->em->flush($entity);
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
            $this->em->flush($entity);
        }
    }
}
