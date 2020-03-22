<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class ShipmentStates
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ShipmentStates
{
    // Common
    const STATE_NEW         = 'new';
    const STATE_CANCELED    = 'canceled';

    // Shipment
    const STATE_PREPARATION = 'preparation';
    const STATE_READY       = 'ready';
    const STATE_SHIPPED     = 'shipped';

    // Return
    const STATE_PENDING     = 'pending';
    const STATE_RETURNED    = 'returned';

    // For sale
    const STATE_NONE        = 'none';
    const STATE_PARTIAL     = 'partial';
    const STATE_COMPLETED   = 'completed';


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
            self::STATE_PREPARATION,
            self::STATE_READY,
            self::STATE_SHIPPED,
            self::STATE_PENDING,
            self::STATE_RETURNED,
            self::STATE_NONE,
            self::STATE_PARTIAL,
            self::STATE_COMPLETED,
        ];
    }

    /**
     * Returns whether the given state is valid or not.
     *
     * @param ShipmentInterface|string $state
     *
     * @return bool
     */
    public static function isValidState($state): bool
    {
        return in_array(self::stateFormShipment($state), self::getStates(), true);
    }

    /**
     * Returns the notifiable states.
     *
     * @return array
     */
    public static function getNotifiableStates(): array
    {
        return [
            self::STATE_READY,
            self::STATE_RETURNED,
            self::STATE_SHIPPED,
        ];
    }

    /**
     * Returns whether the given state is a notifiable state.
     *
     * @param ShipmentInterface|string $state
     *
     * @return bool
     */
    public static function isNotifiableState($state): bool
    {
        return in_array(self::stateFormShipment($state), self::getNotifiableStates(), true);
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
            self::STATE_NONE,
            self::STATE_CANCELED,
        ];
    }

    /**
     * Returns whether the given state is a deletable state.
     *
     * @param ShipmentInterface|string $state
     *
     * @return bool
     */
    public static function isDeletableState($state): bool
    {
        $state = self::stateFormShipment($state);

        return in_array($state, self::getDeletableStates(), true);
    }

    /**
     * Returns the preparable states.
     *
     * @return array
     */
    public static function getPreparableStates(): array
    {
        return [
            self::STATE_NEW,
            self::STATE_NONE,
            self::STATE_PARTIAL,
        ];
    }

    /**
     * Returns whether the given state is a preparable state.
     *
     * @param ShipmentInterface|string $state
     *
     * @return bool
     */
    public static function isPreparableState($state): bool
    {
        $state = self::stateFormShipment($state);

        return in_array($state, self::getPreparableStates(), true);
    }

    /**
     * Returns whether or not the state has changed
     * from a non deletable state to a deletable state.
     *
     * @param array $cs The persistence change set
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
     * Returns whether or not the state has changed
     * from a deletable state to a non deletable state.
     *
     * @param array $cs The persistence change set
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
     * Returns the stockable states.
     *
     * @return array
     */
    public static function getStockableStates(): array
    {
        return [
            self::STATE_READY,
            self::STATE_PARTIAL, // This is a sale state
            self::STATE_SHIPPED,
            self::STATE_RETURNED,
            self::STATE_COMPLETED,
        ];
    }

    /**
     * Returns whether the given state is a stockable state.
     *
     * @param ShipmentInterface|string $state
     *
     * @return bool
     */
    public static function isStockableState($state): bool
    {
        $state = self::stateFormShipment($state);

        return in_array($state, self::getStockableStates(), true);
    }

    /**
     * Returns whether or not the state has changed
     * from a non stockable state to a stockable state.
     *
     * @param array $cs The persistence change set
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
     * @param array $cs The persistence change set
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
     * Returns the shipment state.
     *
     * @param ShipmentInterface|string $stateOrShipment
     *
     * @return string
     */
    private static function stateFormShipment($stateOrShipment): string
    {
        if ($stateOrShipment instanceof ShipmentInterface) {
            $stateOrShipment = $stateOrShipment->getState();
        }

        if (is_string($stateOrShipment) && !empty($stateOrShipment)) {
            return $stateOrShipment;
        }

        throw new InvalidArgumentException("Expected string or instance of " . ShipmentInterface::class);
    }

    /**
     * Returns whether or not the change set is valid.
     *
     * @param array $cs
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

        throw new InvalidArgumentException("Unexpected order state change set.");
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
