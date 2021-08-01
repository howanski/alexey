<?php

namespace App\Service;

use App\Entity\NetworkMachine;
use App\Repository\NetworkMachineRepository;
use Doctrine\ORM\EntityManagerInterface;

class DashboardService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getDashboardData(): array
    {
        $dashboardData = [];

        $dashboardData['refresh_time'] = 30000;

        $networkMachineRepository = $this->getNetworkMachineRepository();
        $dashboardData['machines'] = $networkMachineRepository->findBy(['showOnDashboard' => true]);

        return $dashboardData;
    }

    private function getNetworkMachineRepository(): NetworkMachineRepository
    {
        return $this->em->getRepository(NetworkMachine::class);
    }
}
