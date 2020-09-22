<?php

namespace Ekyna\Component\Commerce\Accounting\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class AccountTypes
 * @package Ekyna\Component\Commerce\Accounting\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class AccountingTypes
{
    const TYPE_GOOD       = 'good';
    //const TYPE_SERVICE    = 'service';
    const TYPE_SHIPPING   = 'shipping';
    const TYPE_TAX        = 'tax';
    const TYPE_PAYMENT    = 'payment';
    const TYPE_UNPAID     = 'unpaid';
    const TYPE_EX_GAIN    = 'exchange_gain';
    const TYPE_EX_LOSS    = 'exchange_loss';
    const TYPE_ADJUSTMENT = 'adjustment';


    /**
     * Returns all the types.
     *
     * @return array
     */
    static public function getTypes(): array
    {
        return [
            static::TYPE_GOOD,
            //static::TYPE_SERVICE,
            static::TYPE_SHIPPING,
            static::TYPE_TAX,
            static::TYPE_PAYMENT,
            static::TYPE_UNPAID,
            static::TYPE_EX_GAIN,
            static::TYPE_EX_LOSS,
            static::TYPE_ADJUSTMENT,
        ];
    }

    /**
     * Returns whether the given type is valid or not.
     *
     * @param string $type
     * @param bool   $throw
     *
     * @return bool
     */
    static public function isValidType(string $type, bool $throw = true): bool
    {
        if (in_array($type, static::getTypes(), true)) {
            return true;
        }

        if ($throw) {
            throw new InvalidArgumentException('Invalid account type.');
        }

        return false;
    }

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
