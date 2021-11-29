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

    public function __construct(
        float $value,
        float $optimum,
        float $greenZoneWidth,
        float $yellowZoneWidth,
    ) {
        if ($optimum < 0) {
            $optimum *= -1;
            $value *= -1;
        }
        if ($value < $this->minValue) {
            $this->minValue = $value;
        }
        $this->greenRight = $optimum + $greenZoneWidth;
        $this->yellowLeft = $optimum - $greenZoneWidth;
        $this->redLeft = $this->yellowLeft - $yellowZoneWidth;
        $this->yellowRight = $this->greenRight + $yellowZoneWidth;
        $this->redRight = $this->yellowRight + $this->redLeft;

        if ($this->redLeft < 0) {
            $offset = 0 - $this->redLeft;
            $this->greenRight += $offset;
            $this->yellowLeft += $offset;
            $this->redLeft += $offset;
            $this->yellowRight += $offset;
            $this->redRight += $offset;
            $value += $offset;
        }
        $this->value = $value;
    }

    public function getXmlResponse(): JsonResponse
    {
        $data = [
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
                'borderWidth' => [0, 0, 0, 0, 0],
            ]],
        ];

        return new JsonResponse(data: $data);
    }
}
