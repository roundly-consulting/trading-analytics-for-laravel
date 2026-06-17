<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Traits;

trait HasPrefix
{
    public string $prefix = '';

    public function prefix(string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function hasPrefix(): bool
    {
        return ! empty($this->prefix);
    }
}
