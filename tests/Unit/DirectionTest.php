<?php

declare(strict_types=1);

use RoundlyConsulting\TradingAnalytics\Enums\Direction;

it('identifies buy direction', function () {
    expect(Direction::BUY->isBuy())->toBeTrue()
        ->and(Direction::BUY->isSell())->toBeFalse();
});

it('identifies sell direction', function () {
    expect(Direction::SELL->isSell())->toBeTrue()
        ->and(Direction::SELL->isBuy())->toBeFalse();
});
