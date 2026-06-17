<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Interfaces;

/**
 * Marks calculators that need more than the single aggregating pass — they
 * collect a per-trade series during the pass and then iterate that series
 * (variance / deviation) in {@see AnalyticsInterface::calculateAfterTrades()}.
 *
 * Keeping these behind a dedicated marker keeps the single-pass aggregate
 * calculators free of any series-storage concerns.
 */
interface MultiPassAnalyticsInterface extends AnalyticsInterface {}
