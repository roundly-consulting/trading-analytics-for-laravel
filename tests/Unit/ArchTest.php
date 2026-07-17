<?php

declare(strict_types=1);

use RoundlyConsulting\Testing\Arch\ArchPresets;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalAggregatesByCurrency;
use RoundlyConsulting\TradingAnalytics\DataTransferObjects\NumericDirectionalByCurrency;

/**
 * The presets replace this file's *generic* rules; the package's domain-specific ones are
 * kept below, because they pin things no preset knows about (a calculator contract, JSON
 * serialisation, an exception hierarchy). The file therefore grows.
 */
ArchPresets::strictTypes('RoundlyConsulting\TradingAnalytics');

/**
 * Replaces two hand-written rules: `result data transfer objects are final` and `leaf
 * data transfer objects are final`, the second of which named its five leaves explicitly.
 * A hand-maintained list of finals rots — it silently fails to cover the next DTO added.
 * Scoping the preset to the whole namespace covers every present and future DTO instead,
 * and inverts the maintenance burden: a new *base* must be exempted deliberately, rather
 * than a new *leaf* being quietly unguarded.
 *
 * The three exemptions are real bases, not oversights: each is extended by the final
 * Results DTOs (e.g. `Wins extends NumericDirectionalByCurrency`), so `final` on them is
 * a fatal error, not a tightening.
 *
 * `finalByDefault` is deliberately NOT applied to the package root. 24 of 54 classes are
 * non-final by design — the calculators extend one another
 * (`NetCumulativeReturn extends GrossCumulativeReturn`) and `Analytics` itself is a
 * documented host extension point (`new static`, and the package's own
 * AnalyticsExtensionTest subclasses it). A rule needing 24 `->ignoring()` entries is a
 * list, not a guard.
 */
ArchPresets::finalByDefault('RoundlyConsulting\TradingAnalytics\DataTransferObjects')
    ->ignoring([
        NumericByCurrency::class,
        NumericDirectionalByCurrency::class,
        NumericDirectionalAggregatesByCurrency::class,
    ]);

/**
 * `swappableModelsAreNotFinal` and `modelsResolveThroughSeam` are not adopted: this
 * package ships no Eloquent model and no `*_model` config key — it is a pure calculation
 * engine over an in-memory collection of trades. Both presets' halves would be inert and
 * green forever without ever being able to fail.
 */

/**
 * This package computes money. The ban is a standing guard against a hashing or
 * randomness scheme being hand-rolled here rather than in crypto-for-laravel — today it
 * uses none, and that is the state worth pinning.
 */
ArchPresets::noLocalCryptoPrimitives('RoundlyConsulting\TradingAnalytics');

/**
 * The Dependency Policy as a test. No `alsoAllow`: this package's `require` ships only
 * php/ext-bcmath/illuminate/roundly, and the workflow installs test tooling with `--dev`,
 * so nothing legitimately lands in `require` that this must forgive. If this goes red,
 * the graph is wrong — never widen the allow-list to quiet it.
 */
ArchPresets::runtimeRequireIsWhitelisted(__DIR__.'/../../composer.json');

/**
 * Replaces `it will not use debugging functions`, which covered dd/dump/ray. The preset
 * adds var_dump and print_r.
 */
ArchPresets::noDebuggingLeftovers();

// ---------------------------------------------------------------------------
// The package's own domain rules. No preset equivalent exists for any of these,
// so they are kept verbatim rather than dropped.
// ---------------------------------------------------------------------------

arch('all calculators implement the analytics calculator interface')
    ->expect('RoundlyConsulting\TradingAnalytics\Analytics')
    ->toImplement('RoundlyConsulting\TradingAnalytics\Interfaces\AnalyticsInterface')
    ->ignoring([
        'RoundlyConsulting\TradingAnalytics\Analytics',
        'RoundlyConsulting\TradingAnalytics\Analytics\Abstraction',
    ]);

arch('result data transfer objects serialize to json')
    ->expect('RoundlyConsulting\TradingAnalytics\DataTransferObjects\Results')
    ->toImplement([
        'Illuminate\Contracts\Support\Arrayable',
        'Illuminate\Contracts\Support\Jsonable',
        'JsonSerializable',
    ]);

arch('enums are final')
    ->expect('RoundlyConsulting\TradingAnalytics\Enums')
    ->toBeEnums();

arch('exceptions extend the package base exception')
    ->expect('RoundlyConsulting\TradingAnalytics\Exceptions')
    ->toExtend('RoundlyConsulting\TradingAnalytics\Exceptions\TradingAnalyticsException');
