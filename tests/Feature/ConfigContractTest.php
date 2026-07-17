<?php

declare(strict_types=1);

/**
 * The config contract trading-analytics never had, pinned in both directions:
 *
 *  - forward — every key the code reads is shipped. This is shops #18, whose whole
 *    store-credit feature read `shops.payments.*` while the file shipped `payment.*`;
 *    330 tests stayed green because the suite set the same wrong key.
 *  - reverse — every shipped leaf is read. A documented key nothing reads is dead config
 *    that lies to the host: media #27's `max_file_size` cap that never applied, alerts
 *    #24's thrice-documented `escalation` key. Both of this package's keys are exactly
 *    that kind of promise — `scale` claims to set bcmath precision for every calculation,
 *    and a host that believes it and is wrong gets silently rounded money.
 */
it('ships exactly the config keys it reads', function (): void {
    expect(__DIR__.'/../../config/trading-analytics.php')->toSatisfyConfigContract(__DIR__.'/../../src', [
        // Both keys are also read through `Analytics::configuredValue($key)`, a seam that
        // guards against an unbooted container before touching the config repository. Its
        // literal lives at the *call site* and the `config()` call itself takes a
        // variable, so the scraper cannot see it as a `config(` read. (A bare
        // `config($var)` is correctly treated as noise rather than flagged as an
        // unverifiable interpolation — right, but it means the read is invisible.)
        //
        // This prefix is what counts those call-site literals as reads. It is *masked*
        // today: the provider's about section happens to read both leaves with literal
        // `config()` calls, so the contract is green without it — measured, not assumed.
        // It is kept because without it the reverse direction silently depends on the
        // about section: stub those two reads out and the contract calls both keys dead
        // while `Analytics` is still reading them. Verified by doing exactly that.
        'extraReadPrefixes' => ['trading-analytics.'],

        // Deliberately NO `excludeFromReverse` for the provider. The testing README's own
        // example excludes the service provider on the grounds that "a render is not a
        // read" — but this provider's `contributesToAbout()` closure calls
        // `config('trading-analytics.…')` for real, and it is one of only two readers in
        // the package. Excluding it would discard readers and weaken the reverse
        // direction for nothing.
    ]);
});
