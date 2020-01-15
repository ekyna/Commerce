<?php

namespace Ekyna\Component\Commerce\Supplier\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;

/**
 * Class SupplierOrderStates
 * @package Ekyna\Component\Commerce\Supplier\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SupplierOrderStates
{
    const STATE_NEW       = 'new';
    const STATE_ORDERED   = 'ordered';
    const STATE_VALIDATED = 'validated';
    const STATE_PARTIAL   = 'partial';
    const STATE_RECEIVED  = 'received';
    const STATE_COMPLETED = 'completed';
    const STATE_CANCELED  = 'canceled';


    /**
     * Returns all the states.
     *
     * @return array
     */
    static public function getStates(): array
    {
        return [
            static::STATE_NEW,
            static::STATE_ORDERED,
            static::STATE_VALIDATED,
            static::STATE_PARTIAL,
            static::STATE_RECEIVED,
            static::STATE_COMPLETED,
            static::STATE_CANCELED,
        ];
    }

    /**
     * Returns the from the given order if not string.
     *
     * @param SupplierOrderInterface|string $state
     *
     * @return string
     */
    static private function stateFromOrder($state): string
    {
        if (is_string($state)) {
            return $state;
        }

        if ($state instanceof SupplierOrderInterface) {
            return $state->getState();
        }

        throw new UnexpectedTypeException($state, ['string', SupplierOrderInterface::class]);
    }

    /**
     * Returns whether the given state is valid or not.
     *
     * @param SupplierOrderInterface|string $state
     *
     * @return bool
     */
    static public function isValidState($state): bool
    {
        return in_array(static::stateFromOrder($state), static::getStates(), true);
    }

    /**
     * Returns the deletable states.
     *
     * @return array
     */
    static public function getDeletableStates(): array
    {
        return [
            static::STATE_NEW,
            static::STATE_CANCELED,
        ];
    }

    /**
     * Returns whether the given state is a deletable state.
     *
     * @param SupplierOrderInterface|string $state
     *
     * @return bool
     */
    static public function isDeletableState($state): bool
    {
        $state = static::stateFromOrder($state);

        return is_null($state) || in_array($state, static::getDeletableStates(), true);
    }

    /**
     * Returns the deletable states.
     *
     * @return array
     */
    static public function getCancelableStates(): array
    {
        return [
            static::STATE_NEW,
            static::STATE_ORDERED,
        ];
    }

    /**
     * Returns whether the given state is a deletable state.
     *
     * @param SupplierOrderInterface|string $state
     *
     * @return bool
     */
    static public function isCancelableState($state): bool
    {
        $state = static::stateFromOrder($state);

        return is_null($state) || in_array($state, static::getCancelableStates(), true);
    }

    /**
     * Returns whether or not the state has changed
     * from a non deletable state to a deletable state.
     *
     * @param array $cs The state persistence change set
     *
     * @return bool
     */
    static public function hasChangedToDeletable(array $cs): bool
    {
        return static::assertValidChangeSet($cs)
            && !static::isDeletableState($cs[0])
            && static::isDeletableState($cs[1]);
    }

    /**
     * Returns whether or not the state has changed
     * from a deletable state to a non deletable state.
     *
     * @param array $cs The state persistence change set
     *
     * @return bool
     */
    static public function hasChangedFromDeletable(array $cs): bool
    {
        return static::assertValidChangeSet($cs)
            && static::isDeletableState($cs[0])
            && !static::isDeletableState($cs[1]);
    }

    /**
     * Returns the states which must result in a stock management.
     *
     * @return array
     */
    static public function getStockableStates(): array
    {
        return [
            static::STATE_VALIDATED,
            static::STATE_PARTIAL,
            static::STATE_RECEIVED,
            static::STATE_COMPLETED,
        ];
    }

    /**
     * Returns whether the given state is a stock state.
     *
     * @param SupplierOrderInterface|string $state
     *
     * @return bool
     */
    static public function isStockableState($state): bool
    {
        $state = static::stateFromOrder($state);

        return !is_null($state) && in_array($state, static::getStockableStates(), true);
    }

    /**
     * Returns whether the state has changed
     * from a non stockable state to a stockable state.
     *
     * @param array $cs The state persistence change set
     *
     * @return bool
     */
    static public function hasChangedToStockable(array $cs): bool
    {
        return static::assertValidChangeSet($cs)
            && !static::isStockableState($cs[0])
            && static::isStockableState($cs[1]);
    }

    /**
     * Returns whether or not the state has changed
     * from a stockable state to a non stockable state.
     *
     * @param array $cs The state persistence change set
     *
     * @return bool
     */
    static public function hasChangedFromStockable(array $cs): bool
    {
        return static::assertValidChangeSet($cs)
            && static::isStockableState($cs[0])
            && !static::isStockableState($cs[1]);
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
    static private function assertValidChangeSet(array $cs): bool
    {
        if (
            array_key_exists(0, $cs) &&
            array_key_exists(1, $cs) &&
            (is_null($cs[0]) || static::isValidState($cs[0])) &&
            (is_null($cs[1]) || static::isValidState($cs[1]))
        ) {
            return true;
        }

        throw new InvalidArgumentException("Unexpected supplier order state change set.");
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
