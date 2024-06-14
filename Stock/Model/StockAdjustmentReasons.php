<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class StockAdjustmentReasons
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class StockAdjustmentReasons
{
    public const REASON_FAULTY   = 'faulty';
    public const REASON_IMPROPER = 'improper';
    public const REASON_DEBIT    = 'debit';
    public const REASON_CREDIT   = 'credit';
    public const REASON_FOUND    = 'found';


    /**
     * Returns all the reasons.
     */
    public static function getReasons(): array
    {
        return [
            self::REASON_FAULTY,
            self::REASON_IMPROPER,
            self::REASON_FOUND,
            self::REASON_CREDIT,
            self::REASON_DEBIT,
        ];
    }

    /**
     * Returns whether the given reason is valid or not.
     */
    public static function isValidReason(StockAdjustmentInterface|string $reason, bool $throw = true): bool
    {
        $reason = self::getReason($reason);

        if (in_array($reason, self::getReasons(), true)) {
            return true;
        }

        if ($throw) {
            throw new InvalidArgumentException('Invalid stock adjustment reason.');
        }

        return false;
    }

    /**
     * Returns all the debit reasons.
     */
    public static function getDebitReasons(): array
    {
        return [
            self::REASON_FAULTY,
            self::REASON_IMPROPER,
            self::REASON_DEBIT,
        ];
    }

    /**
     * Returns whether the given reason is debit or not.
     */
    public static function isDebitReason(StockAdjustmentInterface|string $reason): bool
    {
        $reason = self::getReason($reason);

        self::isValidReason($reason);

        return in_array($reason, self::getDebitReasons(), true);
    }

    private static function getReason(StockAdjustmentInterface|string $value): string
    {
        if ($value instanceof StockAdjustmentInterface) {
            return $value->getReason();
        }

        return $value;
    }

    /**
     * Disabled constructor.
     */
    private function __construct() {

    }
}
