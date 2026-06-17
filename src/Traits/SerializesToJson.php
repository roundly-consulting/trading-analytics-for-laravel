<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Traits;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Bridges a value object's existing toArray() to Laravel's Jsonable and PHP's
 * JsonSerializable contracts, so results drop straight into response()->json(),
 * API resources, and json_encode() without manual conversion.
 *
 * The consuming class must implement toArray(): array<string, mixed> (or a
 * compatible shape), satisfying Illuminate\Contracts\Support\Arrayable.
 *
 * @phpstan-require-implements Arrayable<string, mixed>
 */
trait SerializesToJson
{
    /** @return array<string, mixed> */
    abstract public function toArray(): array;

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options | JSON_THROW_ON_ERROR);
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
