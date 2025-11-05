<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class SupplierOrderStates
 * @package Ekyna\Component\Commerce\Supplier\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SupplierOrderStates
{
    public const STATE_NEW       = 'new';
    public const STATE_ORDERED   = 'ordered';
    public const STATE_VALIDATED = 'validated';
    public const STATE_PARTIAL   = 'partial';
    public const STATE_RECEIVED  = 'received';
    public const STATE_COMPLETED = 'completed';
    public const STATE_CANCELED  = 'canceled';


    /**
     * Returns all the states.
     *
     * @return array
     */
    public static function getStates(): array
    {
        return [
            self::STATE_NEW,
            self::STATE_ORDERED,
            self::STATE_VALIDATED,
            self::STATE_PARTIAL,
            self::STATE_RECEIVED,
            self::STATE_COMPLETED,
            self::STATE_CANCELED,
        ];
    }

    /**
     * Returns the state from the given order if not string.
     *
     * @param SupplierOrderInterface|string $state
     *
     * @return string
     */
    private static function stateFromOrder(SupplierOrderInterface|string $state): string
    {
        if (is_string($state)) {
            return $state;
        }

        return $state->getState();
    }

    /**
     * Returns whether the given state is valid or not.
     *
     * @param SupplierOrderInterface|string $state
     *
     * @return bool
     */
    public static function isValidState(SupplierOrderInterface|string $state): bool
    {
        return in_array(self::stateFromOrder($state), self::getStates(), true);
    }

    /**
     * Returns the deletable states.
     *
     * @return array
     */
    public static function getDeletableStates(): array
    {
        return [
            self::STATE_NEW,
            self::STATE_CANCELED,
        ];
    }

    /**
     * Returns whether the given state is a deletable state.
     *
     * @param SupplierOrderInterface|string $state
     *
     * @return bool
     */
    public static function isDeletableState(SupplierOrderInterface|string $state): bool
    {
        $state = self::stateFromOrder($state);

        return in_array($state, self::getDeletableStates(), true);
    }

    /**
     * Returns the deletable states.
     *
     * @return array
     */
    public static function getCancelableStates(): array
    {
        return [
            self::STATE_NEW,
            self::STATE_ORDERED,
            self::STATE_VALIDATED,
        ];
    }

    /**
     * Returns whether the given state is a deletable state.
     *
     * @param SupplierOrderInterface|string $state
     *
     * @return bool
     */
    public static function isCancelableState(SupplierOrderInterface|string $state): bool
    {
        $state = self::stateFromOrder($state);

        return in_array($state, self::getCancelableStates(), true);
    }

    /**
     * Returns whether the state has changed
     * from a non-deletable state to a deletable state.
     *
     * @param array $cs The state persistence change set
     *
     * @return bool
     */
    public static function hasChangedToDeletable(array $cs): bool
    {
        return self::assertValidChangeSet($cs)
            && !self::isDeletableState($cs[0])
            && self::isDeletableState($cs[1]);
    }

    /**
     * Returns whether the state has changed
     * from a deletable state to a non-deletable state.
     *
     * @param array $cs The state persistence change set
     *
     * @return bool
     */
    public static function hasChangedFromDeletable(array $cs): bool
    {
        return self::assertValidChangeSet($cs)
            && self::isDeletableState($cs[0])
            && !self::isDeletableState($cs[1]);
    }

    /**
     * Returns the states which must result in a stock management.
     *
     * @return array
     */
    public static function getStockableStates(): array
    {
        return [
            self::STATE_VALIDATED,
            self::STATE_PARTIAL,
            self::STATE_RECEIVED,
            self::STATE_COMPLETED,
        ];
    }

    /**
     * Returns whether the given state is a stock state.
     *
     * @param SupplierOrderInterface|string $state
     *
     * @return bool
     */
    public static function isStockableState(SupplierOrderInterface|string $state): bool
    {
        $state = self::stateFromOrder($state);

        return in_array($state, self::getStockableStates(), true);
    }

    /**
     * Returns whether the state has changed
     * from a non stockable state to a stockable state.
     *
     * @param array $cs The state persistence change set
     *
     * @return bool
     */
    public static function hasChangedToStockable(array $cs): bool
    {
        return self::assertValidChangeSet($cs)
            && !self::isStockableState($cs[0])
            && self::isStockableState($cs[1]);
    }

    /**
     * Returns whether or not the state has changed
     * from a stockable state to a non stockable state.
     *
     * @param array $cs The state persistence change set
     *
     * @return bool
     */
    public static function hasChangedFromStockable(array $cs): bool
    {
        return self::assertValidChangeSet($cs)
            && self::isStockableState($cs[0])
            && !self::isStockableState($cs[1]);
    }

    /**
     * Returns whether or not the change set is valid.
     *
     * @param array $cs The state persistence change set
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    private static function assertValidChangeSet(array $cs): bool
    {
        if (
            array_key_exists(0, $cs) &&
            array_key_exists(1, $cs) &&
            (is_null($cs[0]) || self::isValidState($cs[0])) &&
            (is_null($cs[1]) || self::isValidState($cs[1]))
        ) {
            return true;
        }

        throw new InvalidArgumentException('Unexpected supplier order state change set.');
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
