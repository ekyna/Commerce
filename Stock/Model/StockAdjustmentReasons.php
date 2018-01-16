<?php

namespace Ekyna\Component\Commerce\Stock\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class StockAdjustmentReasons
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAdjustmentReasons
{
    const REASON_FAULTY   = 'faulty';
    const REASON_IMPROPER = 'improper';
    const REASON_FOUND    = 'found';


    /**
     * Returns all the reasons.
     *
     * @return array
     */
    static public function getReasons()
    {
        return [
            static::REASON_FAULTY,
            static::REASON_IMPROPER,
            static::REASON_FOUND,
        ];
    }

    /**
     * Returns all the debit reasons.
     *
     * @return array
     */
    static public function getDebitReasons()
    {
        return [
            static::REASON_FAULTY,
            static::REASON_IMPROPER,
        ];
    }

    /**
     * Returns whether the given reason is valid or not.
     *
     * @param string $reason
     * @param bool $throw
     *
     * @return bool
     */
    static public function isValidReason($reason, $throw = true)
    {
        if (in_array($reason, static::getReasons(), true)) {
            return true;
        }

        if ($throw) {
            throw new InvalidArgumentException("Invalid stock adjustment reason.");
        }

        return false;
    }

    /**
     * Returns whether the given reason is debit or not.
     *
     * @param string $reason
     *
     * @return bool
     */
    static public function isDebitReason($reason)
    {
        static::isValidReason($reason);

        return in_array($reason, static::getDebitReasons(), true);
    }
}