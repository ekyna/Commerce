<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class OrderStates
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderStates
{
    public const STATE_NEW       = 'new';
    public const STATE_PENDING   = 'pending';
    public const STATE_REFUSED   = 'refused';
    public const STATE_ACCEPTED  = 'accepted';
    public const STATE_COMPLETED = 'completed';
    public const STATE_REFUNDED  = 'refunded';
    public const STATE_CANCELED  = 'canceled';


    /**
     * Returns all the states.
     */
    public static function getStates(): array
    {
        return [
            self::STATE_NEW,
            self::STATE_PENDING,
            self::STATE_REFUSED,
            self::STATE_ACCEPTED,
            self::STATE_COMPLETED,
            self::STATE_REFUNDED,
            self::STATE_CANCELED,
        ];
    }

    /**
     * Returns whether the given state is valid or not.
     */
    public static function isValidState(?string $state): bool
    {
        return in_array($state, self::getStates(), true);
    }

    /**
     * Returns the deletable states.
     */
    public static function getDeletableStates(): array
    {
        return [
            self::STATE_NEW,
            self::STATE_CANCELED,
            self::STATE_REFUSED,
        ];
    }

    /**
     * Returns whether the given state is a deletable state.
     */
    public static function isDeletableState(?string $state): bool
    {
        return is_null($state) || in_array($state, self::getDeletableStates(), true);
    }

    /**
     * Returns whether the state has changed
     * from a non-deletable state to a deletable state.
     *
     * @param array $cs The persistence change set
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
     * @param array $cs The persistence change set
     */
    static public function hasChangedFromDeletable(array $cs): bool
    {
        return self::assertValidChangeSet($cs)
            && self::isDeletableState($cs[0])
            && !self::isDeletableState($cs[1]);
    }

    /**
     * Returns the states which must result in a stock management.
     */
    public static function getStockableStates(): array
    {
        return [
            self::STATE_ACCEPTED,
            self::STATE_REFUNDED,
            self::STATE_COMPLETED,
        ];
    }

    /**
     * Returns whether the given state is a stock state.
     */
    public static function isStockableState(?string $state): bool
    {
        return !is_null($state) && in_array($state, self::getStockableStates(), true);
    }

    /**
     * Returns whether the state has changed
     * from a non-stockable state to a stockable state.
     *
     * @param array $cs The persistence change set
     */
    public static function hasChangedToStockable(array $cs): bool
    {
        return self::assertValidChangeSet($cs)
            && !self::isStockableState($cs[0])
            && self::isStockableState($cs[1]);
    }

    /**
     * Returns whether the state has changed
     * from a stockable state to a non-stockable state.
     *
     * @param array $cs The persistence change set
     */
    public static function hasChangedFromStockable(array $cs): bool
    {
        return self::assertValidChangeSet($cs)
            && self::isStockableState($cs[0])
            && !self::isStockableState($cs[1]);
    }

    /**
     * Returns whether the change set is valid.
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

        throw new InvalidArgumentException('Unexpected order state change set.');
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
