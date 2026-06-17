<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Traits;

trait HasSuffix
{
    public string $suffix = '';

    public function suffix(string $suffix): self
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function hasSuffix(): bool
    {
        return ! empty($this->suffix);
    }
}
