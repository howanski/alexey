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

    public function pingNetworkMachine(int $id): void
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

    public function wakeOnLan(
        string $wakeDestination,
        string $macAddress,
        int $targetPort = 9,
    ): void {
      $packetData = str_repeat(chr(0xFF), 6);

      $hardwareMac = '';
      foreach (explode(':', strtoupper($macAddress)) as $chunk) {
        $hardwareMac .= chr(hexdec($chunk));
      }
      $packetData .= str_repeat($hardwareMac, 16);

      $socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

      socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, true);
      socket_sendto($socket, $packetData, strlen($packetData), 0, $wakeDestination, $targetPort);

      if ($socket) {
        socket_close($socket);
        unset($socket);
      }
    }
}
