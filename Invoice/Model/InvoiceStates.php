<?php

namespace Ekyna\Component\Commerce\Invoice\Model;

/**
 * Class InvoiceStates
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class InvoiceStates
{
    const STATE_NEW      = 'new';      // Subject does not need invoice yet
    const STATE_PENDING  = 'pending';  // Subject needs invoice but none has been created yet
    const STATE_PARTIAL  = 'partial';  // Subject has invoices but is not fully invoiced
    const STATE_INVOICED = 'invoiced'; // Subject is fully invoiced
    const STATE_CREDITED = 'credited'; // Subject is fully credited


    /**
     * Returns all the states.
     *
     * @return array
     */
    static public function getStates()
    {
        return [
            static::STATE_NEW,
            static::STATE_PENDING,
            static::STATE_PARTIAL,
            static::STATE_INVOICED,
            static::STATE_CREDITED,
        ];
    }

    /**
     * Returns whether the given state is valid or not.
     *
     * @param string $state
     *
     * @return bool
     */
    static public function isValidState($state)
    {
        return in_array($state, static::getStates(), true);
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
