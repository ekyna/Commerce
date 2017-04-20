<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Model;

/**
 * Class InvoiceStates
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class InvoiceStates
{
    public const STATE_NEW       = 'new';       // Subject does not need invoice yet
    public const STATE_CANCELED  = 'canceled';  // Invoicing has been canceled
    public const STATE_PENDING   = 'pending';   // Subject needs invoice but none has been created yet
    public const STATE_PARTIAL   = 'partial';   // Subject has invoices but is not fully invoiced
    public const STATE_INVOICED  = 'invoiced';  // Subject is fully invoiced
    public const STATE_CREDITED  = 'credited';  // Subject is fully credited
    public const STATE_COMPLETED = 'completed'; // Subject is fully completed


    /**
     * Returns all the states.
     *
     * @return array
     */
    public static function getStates(): array
    {
        return [
            self::STATE_NEW,
            self::STATE_CANCELED,
            self::STATE_PENDING,
            self::STATE_PARTIAL,
            self::STATE_INVOICED,
            self::STATE_CREDITED,
            self::STATE_COMPLETED,
        ];
    }

    /**
     * Returns whether the given state is valid or not.
     *
     * @param string $state
     *
     * @return bool
     */
    public static function isValidState(string $state): bool
    {
        return in_array($state, self::getStates(), true);
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
