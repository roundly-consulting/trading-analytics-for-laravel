<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Tests\Feature\Fixtures;

use RoundlyConsulting\TradingAnalytics\Analytics\ProfitFactor;

/**
 * Independently re-computed expectations for a dataset of trade rows.
 *
 * This deliberately does NOT use any of the package's calculators. It re-derives
 * every aggregate from the raw rows with plain PHP + bcmath at the configured
 * scale, so the feature test can assert the package's output against a second,
 * independent implementation (rather than a golden master snapshot).
 *
 * @phpstan-type Row array{
 *     base_currency: string,
 *     quote_currency: string,
 *     direction: string,
 *     open_price: int,
 *     close_price: int,
 *     size: int,
 *     commission: int|null,
 *     opened_at: string,
 *     closed_at: string|null
 * }
 */
final readonly class ExpectedAnalytics
{
    /**
     * @param  array<string, string>  $grossRealizedPnlByQuoteCurrency
     * @param  array<string, string>  $grossRealizedPnlByBaseCurrency
     */
    private function __construct(
        public int $totalCount,
        public int $longCount,
        public int $shortCount,
        public int $openCount,
        public int $closedCount,
        public int $winCount,
        public int $lossCount,
        public int $breakevenCount,
        public string $winRate,
        public string $grossRealizedPnl,
        public string $netRealizedPnl,
        public string $grossBuyRealizedPnl,
        public string $grossSellRealizedPnl,
        public string $totalCommissions,
        public string $realizedCommissions,
        public string $grossProfit,
        public string $grossLoss,
        public string $profitFactor,
        public string $maxDrawdown,
        public array $grossRealizedPnlByQuoteCurrency,
        public array $grossRealizedPnlByBaseCurrency,
    ) {}

    /** The bcmath scale the package uses by default (config `trading-analytics.scale`). */
    public const SCALE = 10;

    /**
     * @param  list<Row>  $rows
     */
    public static function fromRows(array $rows): self
    {
        $totalCount = count($rows);
        $longCount = 0;
        $shortCount = 0;
        $openCount = 0;
        $closedCount = 0;
        $winCount = 0;
        $lossCount = 0;
        $breakevenCount = 0;

        $grossRealizedPnl = '0';
        $netRealizedPnl = '0';
        $grossBuyRealizedPnl = '0';
        $grossSellRealizedPnl = '0';
        $totalCommissions = '0';
        $realizedCommissions = '0';

        // The "unrealized" gross profit/loss buckets the engine feeds into profit
        // factor actually accumulate across EVERY trade (the calculator runs its
        // profit/loss split unconditionally), classified by gross-P&L sign.
        $grossProfit = '0';
        $grossLoss = '0';

        /** @var array<string, string> $grossByQuote */
        $grossByQuote = [];
        /** @var array<string, string> $grossByBase */
        $grossByBase = [];

        // Realized equity curve for max drawdown (net P&L, realized trades only).
        $equity = '0';
        $peak = '0';
        $maxDrawdown = '0';

        foreach ($rows as $row) {
            $isBuy = $row['direction'] === 'buy';
            $isOpen = $row['closed_at'] === null;

            $isBuy ? $longCount++ : $shortCount++;
            $isOpen ? $openCount++ : $closedCount++;

            // Commissions are summed across every trade carrying a commission
            // (open or closed), mirroring the Commissions calculator.
            if ($row['commission'] !== null) {
                $totalCommissions = bcadd($totalCommissions, (string) $row['commission'], self::SCALE);
            }

            $gross = self::grossPnl($row);

            // Win / loss / breakeven are classified on gross P&L, over every trade
            // (the package's Wins calculator does not exclude open positions).
            $sign = bccomp($gross, '0', self::SCALE);
            if ($sign > 0) {
                $winCount++;
                $grossProfit = bcadd($grossProfit, $gross, self::SCALE);
            } elseif ($sign < 0) {
                $lossCount++;
                $grossLoss = bcadd($grossLoss, $gross, self::SCALE);
            } else {
                $breakevenCount++;
            }

            if ($isOpen) {
                continue;
            }

            // Realized trade: contributes to realized P&L, commissions, drawdown.
            $net = self::netPnl($row);

            $grossRealizedPnl = bcadd($grossRealizedPnl, $gross, self::SCALE);
            $netRealizedPnl = bcadd($netRealizedPnl, $net, self::SCALE);

            if ($row['commission'] !== null) {
                $realizedCommissions = bcadd($realizedCommissions, (string) $row['commission'], self::SCALE);
            }

            if ($isBuy) {
                $grossBuyRealizedPnl = bcadd($grossBuyRealizedPnl, $gross, self::SCALE);
            } else {
                $grossSellRealizedPnl = bcadd($grossSellRealizedPnl, $gross, self::SCALE);
            }

            $quote = $row['quote_currency'];
            $base = $row['base_currency'];
            $grossByQuote[$quote] = bcadd($grossByQuote[$quote] ?? '0', $gross, self::SCALE);
            $grossByBase[$base] = bcadd($grossByBase[$base] ?? '0', $gross, self::SCALE);

            // Running peak / trough over the realized equity curve.
            $equity = bcadd($equity, $net, self::SCALE);
            if (bccomp($equity, $peak, self::SCALE) > 0) {
                $peak = $equity;
            } else {
                $drawdown = bcsub($peak, $equity, self::SCALE);
                if (bccomp($drawdown, $maxDrawdown, self::SCALE) > 0) {
                    $maxDrawdown = $drawdown;
                }
            }
        }

        $profitFactor = self::profitFactor($grossProfit, $grossLoss);

        return new self(
            totalCount: $totalCount,
            longCount: $longCount,
            shortCount: $shortCount,
            openCount: $openCount,
            closedCount: $closedCount,
            winCount: $winCount,
            lossCount: $lossCount,
            breakevenCount: $breakevenCount,
            winRate: self::ratio($winCount, $totalCount),
            grossRealizedPnl: self::scaled($grossRealizedPnl),
            netRealizedPnl: self::scaled($netRealizedPnl),
            grossBuyRealizedPnl: self::scaled($grossBuyRealizedPnl),
            grossSellRealizedPnl: self::scaled($grossSellRealizedPnl),
            totalCommissions: self::scaled($totalCommissions),
            realizedCommissions: self::scaled($realizedCommissions),
            grossProfit: self::scaled($grossProfit),
            grossLoss: self::scaled($grossLoss),
            profitFactor: $profitFactor,
            maxDrawdown: self::scaled($maxDrawdown),
            grossRealizedPnlByQuoteCurrency: array_map(self::scaled(...), $grossByQuote),
            grossRealizedPnlByBaseCurrency: array_map(self::scaled(...), $grossByBase),
        );
    }

    /**
     * Gross P&L = (close - open) * size for a buy, (open - close) * size for a sell.
     *
     * @param  Row  $row
     * @return numeric-string
     */
    public static function grossPnl(array $row): string
    {
        $diff = $row['direction'] === 'buy'
            ? bcsub((string) $row['close_price'], (string) $row['open_price'], self::SCALE)
            : bcsub((string) $row['open_price'], (string) $row['close_price'], self::SCALE);

        return bcmul($diff, (string) $row['size'], self::SCALE);
    }

    /**
     * Net P&L = gross P&L minus commission (when present).
     *
     * @param  Row  $row
     * @return numeric-string
     */
    public static function netPnl(array $row): string
    {
        $gross = self::grossPnl($row);

        if ($row['commission'] === null) {
            return $gross;
        }

        return bcsub($gross, (string) $row['commission'], self::SCALE);
    }

    /**
     * Profit factor = gross profit / |gross loss|, at scale 2, mirroring the
     * package's {@see ProfitFactor}.
     *
     * @param  numeric-string  $profit
     * @param  numeric-string  $loss
     */
    private static function profitFactor(string $profit, string $loss): string
    {
        $absLoss = bccomp($loss, '0', self::SCALE) < 0 ? bcmul($loss, '-1', self::SCALE) : $loss;

        if (bccomp($absLoss, '0', 2) === 0) {
            return self::scaled('0', 2);
        }

        return self::scaled(bcdiv($profit, $absLoss, 2), 2);
    }

    /** Integer ratio at scale 2 (wins / total), matching the package's win ratio. */
    private static function ratio(int $numerator, int $denominator): string
    {
        if ($denominator === 0) {
            return self::scaled('0', 2);
        }

        return self::scaled(bcdiv((string) $numerator, (string) $denominator, 2), 2);
    }

    /**
     * Normalise a numeric string to the given scale so equality matches the
     * package's stringified output exactly.
     *
     * @param  numeric-string  $value
     * @return numeric-string
     */
    private static function scaled(string $value, int $scale = self::SCALE): string
    {
        return bcadd($value, '0', $scale);
    }
}
