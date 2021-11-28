<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\CronJob;
use App\Service\AlexeyTranslator;
use Doctrine\ORM\EntityManagerInterface;

final class SystemSettings
{
    public array $cronJobs = [];

    public function __construct(
        private EntityManagerInterface $em,
        private AlexeyTranslator $translator,
    ) {
        $this->cronJobs = [];
        $jobs = $em->getRepository(CronJob::class)->findAll();
        foreach ($jobs as $job) {
            $collectionElementLabel = $this->translator->translateFormHelp(
                field: 'cron_job_' . $job->getJobType(),
                module: 'settings',
            );
            $collectionElementLabel .= ' :';
            $this->cronJobs[str_replace(search: ' ', replace: '_', subject: $collectionElementLabel)] = $job;
        }
    }

    public function save()
    {
        foreach ($this->cronJobs as $cronJob) {
            $this->em->persist($cronJob);
        }
        $this->em->flush();
    }
}
