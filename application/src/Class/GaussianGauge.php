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
        float $optimum,
        float $greenZoneWidth,
        float $yellowZoneWidth,
    ) {

        $this->greenRight = $optimum + $greenZoneWidth;
        $this->yellowLeft = $optimum - $greenZoneWidth;
        $this->redLeft = $this->yellowLeft - $yellowZoneWidth;
        $this->yellowRight = $this->greenRight + $yellowZoneWidth;
        $this->redRight = $this->yellowRight + abs($this->redLeft);

        $this->minValue = $optimum - ($this->redRight - $optimum);
        if ($value < $this->minValue) {
            $this->minValue = $value;
        }
        if ($this->redLeft < $this->minValue) {
            $this->minValue = $this->redLeft;
        }
        $this->value = $value;
    }

    public function setBonusPayload(array $payload)
    {
        $this->bonusPayload = $payload;
    }

    public function getXmlResponse(): JsonResponse
    {
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

        return new JsonResponse(data: $data);
    }
}
