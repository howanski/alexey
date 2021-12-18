<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use App\Class\Interwebz;
use App\Model\TransmissionSettings;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\NetworkStatisticRepository;
use DivisionByZeroError;

#[ORM\Entity(repositoryClass: NetworkStatisticRepository::class)]
class NetworkStatistic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private int $id;

    #[ORM\Column(type: 'datetime')]
    private DateTime $probingTime;

    #[ORM\Column(type: 'bigint')]
    private int $dataUploadedInFrame = 0;

    #[ORM\Column(type: 'bigint')]
    private int $dataDownloadedInFrame = 0;

    private NetworkStatistic $referencePoint;

    #[ORM\ManyToOne(targetEntity: NetworkStatisticTimeFrame::class, inversedBy: 'networkStatistics')]
    #[ORM\JoinColumn(nullable: false)]
    private NetworkStatisticTimeFrame $timeFrame;

    public function __construct()
    {
        $this->probingTime = new DateTime('now');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProbingTime(): DateTime
    {
        return $this->probingTime;
    }

    public function setProbingTime(DateTime $probingTime): self
    {
        $this->probingTime = $probingTime;
        return $this;
    }

    public function getDataUploadedInFrame(): int
    {
        return $this->dataUploadedInFrame;
    }

    public function setDataUploadedInFrame(int $dataUploadedInFrame): self
    {
        $this->dataUploadedInFrame = $dataUploadedInFrame;
        return $this;
    }

    public function getDataDownloadedInFrame(): int
    {
        return $this->dataDownloadedInFrame;
    }

    public function setDataDownloadedInFrame(int $dataDownloadedInFrame): self
    {
        $this->dataDownloadedInFrame = $dataDownloadedInFrame;
        return $this;
    }

    public function getTimeFrame(): NetworkStatisticTimeFrame
    {
        return $this->timeFrame;
    }

    public function setTimeFrame(NetworkStatisticTimeFrame $timeFrame): self
    {
        $this->timeFrame = $timeFrame;
        return $this;
    }

    public function setReferencePoint(NetworkStatistic $referencePoint): self
    {
        $this->referencePoint = $referencePoint;
        return $this;
    }

    // Time

    public function getTimeLeftTillFrameEnd(): int
    {
        $startTime = $this->getProbingTime()->getTimestamp();
        $endTime = $this->getTimeFrame()->getBillingFrameEnd()->getTimestamp();
        return ($endTime - $startTime);
    }

    private function getTimeLeftTillMidnight(): int
    {
        $probingTimeClone = clone $this->getProbingTime();
        $probingTimeClone->setTime(23, 59, 59);
        $midnight = $probingTimeClone->getTimestamp();
        return $midnight - $this->getProbingTime()->getTimestamp();
    }

    // seconds passed
    public function getTimePassedFromReferencePoint(): int
    {
        $startTime = $this->referencePoint->getProbingTime()->getTimestamp();
        $endTime = $this->getProbingTime()->getTimestamp();
        return ($endTime - $startTime);
    }

    // Download

    public function getDataDownloadedFromReferencePoint(): int
    {
        return ($this->getDataDownloadedInFrame() - $this->referencePoint->getDataDownloadedInFrame());
    }

    public function getDownloadSpeedFromReferencePoint(): float
    {
        return ($this->getDataDownloadedFromReferencePoint() / $this->getTimePassedFromReferencePoint());
    }

    public function getDownloadSpeedFromReferencePointReadable(): string
    {
        return Interwebz::formatBytes((int)$this->getDownloadSpeedFromReferencePoint()) . '/s';
    }

    // Upload

    public function getDataUploadedFromReferencePoint(): int
    {
        return ($this->getDataUploadedInFrame() - $this->referencePoint->getDataUploadedInFrame());
    }

    public function getUploadSpeedFromReferencePoint(): float
    {
        return ($this->getDataUploadedFromReferencePoint() / $this->getTimePassedFromReferencePoint());
    }

    public function getUploadSpeedFromReferencePointReadable(): string
    {
        return Interwebz::formatBytes((int)$this->getUploadSpeedFromReferencePoint()) . '/s';
    }

    // Download + Upload

    public function getTotalTrafficFromReferencePoint(): int
    {
        return ($this->getDataDownloadedFromReferencePoint() + $this->getDataUploadedFromReferencePoint());
    }

    public function getTotalTrafficFromReferencePointReadable(): string
    {
        return Interwebz::formatBytes($this->getTotalTrafficFromReferencePoint());
    }

    public function getTotalSpeedFromReferencePoint(): float
    {
        return ($this->getDownloadSpeedFromReferencePoint() + $this->getUploadSpeedFromReferencePoint());
    }

    public function getTotalSpeedFromReferencePointReadable(): string
    {
        return Interwebz::formatBytes($this->getTotalSpeedFromReferencePoint()) . '/s';
    }

    public function getTrafficLeft(): int
    {
        return ($this->getTimeFrame()->getBillingFrameDataLimit() -
            ($this->getDataUploadedInFrame() + $this->getDataDownloadedInFrame()));
    }

    private function getTrafficLeftTillMidnight(): int
    {
        $packageSize = $this->getTimeFrame()->getBillingFrameDataLimit();
        $packageEnd = $this->getTimeFrame()->getBillingFrameEnd()->getTimestamp();
        $packageStart = $this->getTimeFrame()->getBillingFrameStart()->getTimestamp();
        $packageLength =  $packageEnd - $packageStart;
        $continuumDensity = $packageSize / $packageLength;
        $timeTillMidnight = $this->getTimeLeftTillMidnight();
        $timeTillFrameEnd = $this->getTimeLeftTillFrameEnd();
        $timeFromMidnightToFrameEnd = $timeTillFrameEnd - $timeTillMidnight;
        $packageFromMidnightToFrameEnd = (int)($timeFromMidnightToFrameEnd * $continuumDensity);
        return $this->getTrafficLeft() - $packageFromMidnightToFrameEnd;
    }

    public function getTrafficLeftReadable(
        int $precision = 2,
        string $frameWidth = TransmissionSettings::TARGET_SPEED_FRAME_FULL,
    ): string {
        if (TransmissionSettings::TARGET_SPEED_FRAME_DAY === $frameWidth) {
            $value = $this->getTrafficLeftTillMidnight();
        } else {
            $value = $this->getTrafficLeft();
        }
        return Interwebz::formatBytes($value, $precision);
    }

    public function getTransferRateLeft($frameWidth = TransmissionSettings::TARGET_SPEED_FRAME_FULL): float
    {
        try {
            if (TransmissionSettings::TARGET_SPEED_FRAME_DAY === $frameWidth) {
                return ($this->getTrafficLeftTillMidnight() / $this->getTimeLeftTillMidnight());
            } else {
                return ($this->getTrafficLeft() / $this->getTimeLeftTillFrameEnd());
            }
        } catch (DivisionByZeroError) {
            return 0;
        }
    }

    public function getTransferRateLeftReadable(
        int $precision = 2,
        $frameWidth = TransmissionSettings::TARGET_SPEED_FRAME_FULL
    ): string {
        return Interwebz::formatBytes((int)$this->getTransferRateLeft($frameWidth), $precision) . '/s';
    }
}
