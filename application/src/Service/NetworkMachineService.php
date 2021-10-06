<?php

declare(strict_types=1);

namespace App\Service;

use JJG\Ping;
use App\Entity\NetworkMachine;
use App\Repository\NetworkMachineRepository;
use Doctrine\ORM\EntityManagerInterface;

class NetworkMachineService
{
    public function __construct(
        private EntityManagerInterface $em,
        private NetworkMachineRepository $networkMachineRepository,
    ) {
    }

    public function pingMachines()
    {
        $networkMachines = $this->networkMachineRepository->findAll();

        /**
         * @var NetworkMachine $networkMachine
         */
        foreach ($networkMachines as $networkMachine) {
            $uri = $networkMachine->getUri();
            $ping = new Ping(host: $uri, ttl: 255, timeout: 2);
            $latency = $ping->ping();
            if (is_float($latency)) {
                $networkMachine->setStatus(NetworkMachine::STATUS_REACHABLE);
                $now = new \DateTime();
                $networkMachine->setLastSeen($now);
            } else {
                $networkMachine->setStatus(NetworkMachine::STATUS_UNREACHABLE);
            }
            $this->em->persist($networkMachine);
            $this->em->flush($networkMachine);
        }
    }
}
