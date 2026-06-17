<?php

declare(strict_types=1);

namespace RoundlyConsulting\TradingAnalytics\Exceptions;

use RuntimeException;

/**
 * Base exception for every error thrown by the package, so consumers can
 * `catch (TradingAnalyticsException $e)` to trap all of them at once.
 */
abstract class TradingAnalyticsException extends RuntimeException {}
