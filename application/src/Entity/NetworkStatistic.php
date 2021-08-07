<?php

namespace App\Entity;

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
     * @ORM\Column(type="datetime")
     * @var DateTime
     */
    private $billingFrameStart;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTime
     */
    private $billingFrameEnd;

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
     * @ORM\Column(type="bigint")
     * @var int
     */
    private $billingFrameDataLimit = 0;

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
     * @return  DateTime
     */
    public function getBillingFrameStart()
    {
        return $this->billingFrameStart;
    }

    /**
     * @param  DateTime  $billingFrameStart
     *
     * @return  self
     */
    public function setBillingFrameStart(DateTime $billingFrameStart)
    {
        $this->billingFrameStart = $billingFrameStart;

        return $this;
    }

    /**
     * @return  DateTime
     */
    public function getBillingFrameEnd()
    {
        return $this->billingFrameEnd;
    }

    /**
     * @param  DateTime  $billingFrameEnd
     *
     * @return  self
     */
    public function setBillingFrameEnd(DateTime $billingFrameEnd)
    {
        $this->billingFrameEnd = $billingFrameEnd;

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

    /**
     * @return  int
     */
    public function getBillingFrameDataLimit()
    {
        return $this->billingFrameDataLimit;
    }

    /**
     * @param  int  $billingFrameDataLimit
     *
     * @return  self
     */
    public function setBillingFrameDataLimit(int $billingFrameDataLimit)
    {
        $this->billingFrameDataLimit = $billingFrameDataLimit;

        return $this;
    }
}
