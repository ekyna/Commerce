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
    const TYPE_GOOD     = 'good';
    //const TYPE_SERVICE  = 'service';
    const TYPE_SHIPPING = 'shipping';
    const TYPE_TAX      = 'tax';
    const TYPE_PAYMENT  = 'payment';


    /**
     * Returns all the types.
     *
     * @return array
     */
    static public function getTypes()
    {
        return [
            static::TYPE_GOOD,
            //static::TYPE_SERVICE,
            static::TYPE_SHIPPING,
            static::TYPE_TAX,
            static::TYPE_PAYMENT,
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
    static public function isValidType($type, $throw = true)
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
