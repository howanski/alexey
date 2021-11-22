<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\NetworkMachine;
use App\Message\AsyncJob;
use App\Repository\NetworkMachineRepository;
use Doctrine\ORM\EntityManagerInterface;
use JJG\Ping;
use Symfony\Component\Messenger\MessageBusInterface;

final class NetworkMachineService
{
    public function __construct(
        private EntityManagerInterface $em,
        private NetworkMachineRepository $networkMachineRepository,
        private MessageBusInterface $bus,
    ) {
    }

    public function schedulePinging(): void
    {
        $machines = $this->networkMachineRepository->findAll();
        /** @var NetworkMachine $machine */
        foreach ($machines as $machine) {
            $this->bus->dispatch(new AsyncJob(
                jobType: AsyncJob::TYPE_PING_MACHINE,
                payload: [
                    'id' => $machine->getId(),
                ],
            ));
        }
    }

    public function pingNetworkMachine(int $id)
    {
        $networkMachine = $this->networkMachineRepository->find($id);
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
        $this->em->flush();
    }

    public function wakeOnLan(string $wakeDestination, string $macAddress)
    {
        exec('wakeonlan -i ' . $wakeDestination . ' ' . $macAddress);
        exec('wakeonlan -i ' . $wakeDestination . ' ' . $macAddress);
        exec('wakeonlan -i ' . $wakeDestination . ' ' . $macAddress);
    }
}
