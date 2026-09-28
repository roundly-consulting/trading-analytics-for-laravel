<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Interfaces;

/**
 * Marks calculators whose figures follow the order realized trades close in — the equity
 * curve behind the maximum drawdown, winning / losing streaks, the running cumulative return.
 *
 * While one of them runs, the engine requires realized trades to arrive in close-time order
 * and throws on the first one that does not, rather than report figures for a sequence that
 * never happened. Sorting inside the engine would mean holding the whole history in memory.
 */
interface SequentialAnalyticsInterface extends AnalyticsInterface {}
