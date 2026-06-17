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

arch('result data transfer objects are final')
    ->expect('RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results')
    ->toBeFinal();

arch('leaf data transfer objects are final')
    ->expect([
        'RoundlyConsulting\TradingAnalytics\DataTransferObjects\Trade',
        'RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericValueAsString',
        'RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericAggregates',
        'RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericByDirections',
        'RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregates',
    ])
    ->toBeFinal();

arch('enums are final')
    ->expect('RoundlyConsulting\TradingAnalytics\Enums')
    ->toBeEnums();

arch('exceptions extend the package base exception')
    ->expect('RoundlyConsulting\TradingAnalytics\Exceptions')
    ->toExtend('RoundlyConsulting\TradingAnalytics\Exceptions\TradingAnalyticsException');
