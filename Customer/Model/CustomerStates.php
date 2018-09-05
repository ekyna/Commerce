<?php

namespace Ekyna\Component\Commerce\Customer\Model;

/**
 * Class CustomerStates
 * @package Ekyna\Component\Commerce\Customer\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CustomerStates
{
    const STATE_NEW       = 'new';
    const STATE_VALID     = 'valid';
    const STATE_FRAUDSTER = 'fraudster';


    /**
     * Returns the customer states.
     *
     * @return array
     */
    static public function getStates()
    {
        return [
            static::STATE_NEW,
            static::STATE_VALID,
            static::STATE_FRAUDSTER,
        ];
    }

    /**
     * Returns whether the given line type is valid or not.
     *
     * @param string $type
     *
     * @return bool
     */
    static public function isValidType($type)
    {
        return in_array($type, static::getStates(), true);
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
