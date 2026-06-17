<?php

declare(strict_types=1);

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch('all calculators implement the analytics calculator interface')
    ->expect('RoundlyConsulting\TradingAnalytics\Analytics')
    ->toImplement('RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface')
    ->ignoring([
        'RoundlyConsulting\TradingAnalytics\Analytics',
        'RoundlyConsulting\TradingAnalytics\Analytics\Abstraction',
    ]);
