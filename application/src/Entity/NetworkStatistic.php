<?php

namespace App\Entity;

use App\Class\HHelpers;
use App\Repository\NetworkStatisticRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NetworkStatisticRepository::class)
 */
class NetworkStatistic
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTime
     */
    private $probingTime;

    /**
     * @ORM\Column(type="bigint")
     * @var int
     */
    private $dataUploadedInFrame = 0;

    /**
     * @ORM\Column(type="bigint")
     * @var int
     */
    private $dataDownloadedInFrame = 0;

    /**
     * @var NetworkStatistic
     */
    private $referencePoint;

    /**
     * @ORM\ManyToOne(targetEntity=NetworkStatisticTimeFrame::class, inversedBy="networkStatistics")
     * @ORM\JoinColumn(nullable=false)
     */
    private $timeFrame;

    public function __construct()
    {
        $this->probingTime = new DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return  DateTime
     */
    public function getProbingTime()
    {
        return $this->probingTime;
    }

    /**
     * @param  DateTime  $probingTime
     *
     * @return  self
     */
    public function setProbingTime(DateTime $probingTime)
    {
        $this->probingTime = $probingTime;

        return $this;
    }

    /**
     * @return  int
     */
    public function getDataUploadedInFrame()
    {
        return $this->dataUploadedInFrame;
    }

    /**
     * @param  int  $dataUploadedInFrame
     *
     * @return  self
     */
    public function setDataUploadedInFrame(int $dataUploadedInFrame)
    {
        $this->dataUploadedInFrame = $dataUploadedInFrame;

        return $this;
    }

    /**
     * @return  int
     */
    public function getDataDownloadedInFrame()
    {
        return $this->dataDownloadedInFrame;
    }

    /**
     * @param  int  $dataDownloadedInFrame
     *
     * @return  self
     */
    public function setDataDownloadedInFrame(int $dataDownloadedInFrame)
    {
        $this->dataDownloadedInFrame = $dataDownloadedInFrame;

        return $this;
    }

    public function getTimeFrame(): ?NetworkStatisticTimeFrame
    {
        return $this->timeFrame;
    }

    public function setTimeFrame(?NetworkStatisticTimeFrame $timeFrame): self
    {
        $this->timeFrame = $timeFrame;

        return $this;
    }

    /**
     * @param  NetworkStatistic  $referencePoint
     *
     * @return  self
     */
    public function setReferencePoint(NetworkStatistic $referencePoint)
    {
        $this->referencePoint = $referencePoint;

        return $this;
    }

    private function ensureReferencePointSet()
    {
        /**
         * If no reference point set, measures are done on empty statistic set on $timeFrame start
         */
        if (!($this->referencePoint instanceof NetworkStatistic)) {
            $referencePoint = new NetworkStatistic();
            $timeFrame = $this->getTimeFrame();
            $referencePoint->setTimeFrame($timeFrame);
            $referencePoint->setProbingTime($timeFrame->getBillingFrameStart());
            $referencePoint->setDataUploadedInFrame(0);
            $referencePoint->setDataDownloadedInFrame(0);
            $this->setReferencePoint($referencePoint);
        }
    }

    /**
     * Time
     */

    public function getTimeLeftTillFrameEnd(): int
    {
        $startTime = $this->getProbingTime()->getTimestamp();
        $endTime = $this->getTimeFrame()->getBillingFrameEnd()->getTimestamp();
        return ($endTime - $startTime);
    }

    /**
     * @return integer value of seconds passed
     */
    public function getTimePassedFromReferencePoint(): int
    {
        $this->ensureReferencePointSet();
        $startTime = $this->referencePoint->getProbingTime()->getTimestamp();
        $endTime = $this->getProbingTime()->getTimestamp();
        return ($endTime - $startTime);
    }

    /**
     * Download
     */

    public function getDataDownloadedFromReferencePoint(): int
    {
        $this->ensureReferencePointSet();
        return ($this->getDataDownloadedInFrame() - $this->referencePoint->getDataDownloadedInFrame());
    }

    public function getDownloadSpeedFromReferencePoint(): float
    {
        return ($this->getDataDownloadedFromReferencePoint() / $this->getTimePassedFromReferencePoint());
    }

    public function getDownloadSpeedFromReferencePointReadable(): string
    {
        return HHelpers::formatBytes((int)$this->getDownloadSpeedFromReferencePoint()) . '/s';
    }

    /**
     * Upload
     */

    public function getDataUploadedFromReferencePoint(): int
    {
        $this->ensureReferencePointSet();
        return ($this->getDataUploadedInFrame() - $this->referencePoint->getDataUploadedInFrame());
    }

    public function getUploadSpeedFromReferencePoint(): float
    {
        return ($this->getDataUploadedFromReferencePoint() / $this->getTimePassedFromReferencePoint());
    }

    public function getUploadSpeedFromReferencePointReadable(): string
    {
        return HHelpers::formatBytes((int)$this->getUploadSpeedFromReferencePoint()) . '/s';
    }

    /**
     * Download + Upload
     */

    public function getTotalTrafficFromReferencePoint(): int
    {
        return ($this->getDataDownloadedFromReferencePoint() + $this->getDataUploadedFromReferencePoint());
    }

    public function getTotalTrafficFromReferencePointReadable(): string
    {
        return HHelpers::formatBytes($this->getTotalTrafficFromReferencePoint());
    }

    public function getTotalSpeedFromReferencePoint(): float
    {
        return ($this->getDownloadSpeedFromReferencePoint() + $this->getUploadSpeedFromReferencePoint());
    }

    public function getTotalSpeedFromReferencePointReadable(): string
    {
        return HHelpers::formatBytes($this->getTotalSpeedFromReferencePoint()) . '/s';
    }

    public function getTrafficLeft(): int
    {
        return ($this->getTimeFrame()->getBillingFrameDataLimit() - ($this->getDataUploadedInFrame() + $this->getDataDownloadedInFrame()));
    }

    public function getTrafficLeftReadable(): string
    {
        return HHelpers::formatBytes($this->getTrafficLeft());
    }

    public function getTransferRateLeft(): float
    {
        return ($this->getTrafficLeft() / $this->getTimeLeftTillFrameEnd());
    }

    public function getTransferRateLeftReadable(): string
    {
        return HHelpers::formatBytes((int)$this->getTransferRateLeft()) . '/s';
    }
}
