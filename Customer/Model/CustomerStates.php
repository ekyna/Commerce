<?php

namespace Ekyna\Component\Commerce\Customer\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

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
    static public function getStates(): array
    {
        return [
            static::STATE_NEW,
            static::STATE_VALID,
            static::STATE_FRAUDSTER,
        ];
    }

    /**
     * Returns whether the given state is valid or not.
     *
     * @param string $state
     * @param bool   $throw
     *
     * @return bool
     */
    static public function isValid(string $state, bool $throw = true): bool
    {
        if (in_array($state, static::getStates(), true)) {
            return true;
        }

        if ($throw) {
            throw new InvalidArgumentException('Invalid notification type.');
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
