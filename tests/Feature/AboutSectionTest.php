<?php

declare(strict_types=1);

/**
 * The secret-safe `about` capture (A).
 *
 * Purchases #13 is the bug this exists for: the fleet's most credential-heavy `about`
 * section was guarded by negative assertions against `app(Kernel::class)->output()`,
 * which returns `''`. Every "does not leak" check was vacuous — passing against empty
 * output. This capture goes through `Artisan::call('about')` and asserts the output is
 * non-empty and renders every `mustRender` string *before* it looks for a secret, so a
 * negative-only check can never pass over nothing.
 *
 * Trading-analytics holds no credential at all, which is exactly why the *positive* half
 * carries this test: the section reports the configured decimal scale, and the risk is a
 * row that silently renders nothing (or renders 'DEFAULT' when a scale really is set) —
 * a section that lies about the precision every P&L figure is computed at. The
 * `mustRender` list is what pins that the rows actually report.
 */
it('renders the trading-analytics section and reports the configured scale', function (): void {
    config([
        'trading-analytics.scale' => 18,
        'trading-analytics.win_rate_period' => 'weekly',
    ]);

    expect('trading-analytics')->toLeakNoSecrets(
        secrets: [
            // Nothing about a host's positions belongs in `about`. These would only
            // appear if a future change started rendering computed figures rather than
            // configuration — the section reports settings, never trades.
            'BTC',
            'openPrice',
        ],
        mustRender: [
            'Decimal scale',
            'Win-rate period',
            // The positive halves: the configured values must really reach the section.
            // Without these the check would pass just as happily over a blank row.
            '18',
            'weekly',
        ],
    );
});

/**
 * The section's INVALID branch: an unrecognised period renders as INVALID rather than as a
 * default, mirroring the engine, which throws on it. Pinned here because it is the one place
 * the section's output is a *decision*, not a value.
 */
it('reports an unrecognised win-rate period as INVALID (strict config)', function (): void {
    config(['trading-analytics.win_rate_period' => 'fortnightly']);

    expect('trading-analytics')->toLeakNoSecrets(
        secrets: [],
        mustRender: ['Win-rate period', 'INVALID'],
    );
});
