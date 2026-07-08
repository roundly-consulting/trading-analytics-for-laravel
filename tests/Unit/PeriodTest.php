<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use RoundlyConsulting\Enums\DataTransferObjects\EnumOption;
use RoundlyConsulting\Enums\Exceptions\EnumException;
use RoundlyConsulting\TradingAnalytics\Enums\Period;

it('buckets a moment per period', function (Period $period, string $expected) {
    $moment = Carbon::create(2024, 8, 6, 14, 30); // a Tuesday

    expect($period->bucketFor($moment))->toBe($expected);
})->with([
    [Period::DAILY, '2024-08-06'],
    [Period::WEEKLY, '2024-W32'],
    [Period::MONTHLY, '2024-08'],
]);

it('returns the period values as a collection', function () {
    expect(Period::values()->all())->toBe(['daily', 'weekly', 'monthly']);
});

it('exposes the enum names', function () {
    expect(Period::names()->all())->toBe(['DAILY', 'WEEKLY', 'MONTHLY']);
});

it('exposes readable labels', function () {
    expect(Period::labels()->all())->toBe(['Daily', 'Weekly', 'Monthly']);
});

it('builds a value => label option map', function () {
    expect(Period::toOptions()->all())->toBe([
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
    ]);
});

it('builds option DTOs', function () {
    $options = Period::options();

    expect($options)->toHaveCount(3)
        ->and($options->first())->toBeInstanceOf(EnumOption::class)
        ->and($options->first()->value)->toBe('daily')
        ->and($options->first()->label)->toBe('Daily')
        ->and($options->first()->name)->toBe('DAILY');
});

it('builds a validation rule', function () {
    expect(Period::validationRule())->toBe('in:daily,weekly,monthly');
});

it('resolves a case per readable label', function (Period $case, string $label) {
    expect($case->readable())->toBe($label);
})->with([
    [Period::DAILY, 'Daily'],
    [Period::WEEKLY, 'Weekly'],
    [Period::MONTHLY, 'Monthly'],
]);

it('resolves a case from its name', function () {
    expect(Period::fromName('WEEKLY'))->toBe(Period::WEEKLY);
});

it('resolves a case from its label', function () {
    expect(Period::tryFromLabel('Daily'))->toBe(Period::DAILY);
});

it('throws resolving an unknown label', function () {
    Period::fromLabel('nope');
})->throws(EnumException::class);

it('returns null resolving an unknown value', function () {
    expect(Period::tryFrom('yearly'))->toBeNull();
});
