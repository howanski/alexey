<?php

declare(strict_types=1);

namespace App\Class;

use Symfony\Component\HttpFoundation\JsonResponse;

final class GaussianGauge
{
    private float $redLeft;
    private float $yellowLeft;
    private float $greenRight;
    private float $yellowRight;
    private float $redRight;

    private float $value;
    private float $minValue = 0.0;

    private array $bonusPayload = [];

    public function __construct(
        float $value,
        private float $optimum,
        float $greenZoneWidth,
        float $yellowZoneWidth,
        private bool $leftHalf,
    ) {
        if (true === $leftHalf) {
            $fraction = ($greenZoneWidth + $yellowZoneWidth) * 0.3;
        } else {
            $fraction = ($greenZoneWidth + $yellowZoneWidth) * 0.6;
        }

        $this->greenRight = $optimum + $greenZoneWidth;
        $this->yellowLeft = $optimum - $greenZoneWidth;
        $this->redLeft = $this->yellowLeft - $yellowZoneWidth;
        $this->minValue = $this->redLeft - $fraction;
        $this->yellowRight = $this->greenRight + $yellowZoneWidth;
        $this->redRight = $this->yellowRight + $fraction;

        if ($value < $this->minValue) {
            $this->minValue = $value;
        }

        if ($value > $this->optimum) {
            $this->optimum = $value;
        }

        $this->value = $value;
    }

    public function setBonusPayload(array $payload)
    {
        $this->bonusPayload = $payload;
    }

    public function getXmlResponse(): JsonResponse
    {
        if (true === $this->leftHalf) {
            $data = [
                'labels' => ['', '', '', '', ''],
                'datasets' => [[
                    'value' => $this->value,
                    'minValue' => $this->minValue,
                    'data' => [
                        $this->redLeft,
                        $this->yellowLeft,
                        $this->optimum,
                    ],
                    'backgroundColor' => ['#bf616a', '#ebcb8b', '#a3be8c'],
                    'borderColor' => ['#2e3440', '#2e3440', '#2e3440'],
                    'borderWidth' => [2, 2, 2],
                ]],
                'bonusPayload' => $this->bonusPayload,
            ];
        } else {
            $data = [
                'labels' => ['', '', '', '', ''],
                'datasets' => [[
                    'value' => $this->value,
                    'minValue' => $this->minValue,
                    'data' => [
                        $this->redLeft,
                        $this->yellowLeft,
                        $this->greenRight,
                        $this->yellowRight,
                        $this->redRight,
                    ],
                    'backgroundColor' => ['#bf616a', '#ebcb8b', '#a3be8c', '#ebcb8b', '#bf616a'],
                    'borderColor' => ['#2e3440', '#2e3440', '#2e3440', '#2e3440', '#2e3440'],
                    'borderWidth' => [2, 2, 2, 2, 2],
                ]],
                'bonusPayload' => $this->bonusPayload,
            ];
        }


        return new JsonResponse(data: $data);
    }
}
