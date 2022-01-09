<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\CronJob;
use App\Service\AlexeyTranslator;
use App\Service\SimpleSettingsService;
use Doctrine\ORM\EntityManagerInterface;

final class SystemSettings
{
    public const TUNNELING_ALLOWED = 'TUNNELING_ALLOWED';

    public array $cronJobs = [];
    public string $tunnelingAllowed = SimpleSettingsService::UNIVERSAL_FALSE;

    public function __construct(
        private EntityManagerInterface $em,
        private AlexeyTranslator $translator,
        private SimpleSettingsService $simpleSettingsService,
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
        $tun = $simpleSettingsService->getSettings([self::TUNNELING_ALLOWED], null);
        $this->tunnelingAllowed = $tun[self::TUNNELING_ALLOWED];
    }

    public function save()
    {
        foreach ($this->cronJobs as $cronJob) {
            $this->em->persist($cronJob);
        }
        $this->simpleSettingsService->saveSettings(
            [
                self::TUNNELING_ALLOWED => $this->tunnelingAllowed,
            ],
            null
        );
        $this->em->flush();
    }
}
