<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Traits;

use RoundlyConsulting\TradingAnalytics\Exceptions\InvalidScaleException;

trait HasScale
{
    protected int $scale = 10;

    public function scale(int $scale): static
    {
        if ($scale < 0) {
            throw InvalidScaleException::negative($scale);
        }

        $this->scale = $scale;

        return $this;
    }

    public function getScale(): int
    {
        return $this->scale;
    }
}
