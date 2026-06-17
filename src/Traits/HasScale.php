<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Traits;

trait HasScale
{
    protected int $scale = 10;

    public function scale(int $scale): self
    {
        $this->scale = $scale;

        return $this;
    }

    public function getScale(): int
    {
        return $this->scale;
    }
}
