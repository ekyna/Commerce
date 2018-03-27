<?php

namespace Ekyna\Component\Commerce\Payment\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class PaymentTermTriggers
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PaymentTermTriggers
{
    const TRIGGER_SHIPPED        = 'shipped';
    const TRIGGER_FULLY_SHIPPED  = 'fully_shipped';
    const TRIGGER_INVOICED       = 'invoiced';
    const TRIGGER_FULLY_INVOICED = 'fully_invoiced';


    /**
     * Returns all the triggers.
     *
     * @return array
     */
    static public function getTriggers()
    {
        return [
            static::TRIGGER_SHIPPED,
            static::TRIGGER_FULLY_SHIPPED,
            static::TRIGGER_INVOICED,
            static::TRIGGER_FULLY_INVOICED,
        ];
    }

    /**
     * Returns whether or not the given trigger is valid.
     *
     * @param string $trigger
     * @param bool   $throwException
     *
     * @return bool
     */
    static public function isValidTrigger($trigger, $throwException = true)
    {
        if (in_array($trigger, static::getTriggers(), true)) {
            return true;
        }

        if ($throwException) {
            throw new InvalidArgumentException("Invalid payment term trigger '$trigger'.");
        }

        return false;
    }

    /**
     * Disabled constructor.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}
