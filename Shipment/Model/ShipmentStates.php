<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface as Shipment;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface as Subject;

use function array_key_exists;
use function in_array;
use function is_null;

/**
 * Class ShipmentStates
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ShipmentStates
{
    // Common
    public const STATE_NEW      = 'new';
    public const STATE_CANCELED = 'canceled';

    // Shipment
    public const STATE_PREPARATION = 'preparation';
    public const STATE_READY       = 'ready';
    public const STATE_SHIPPED     = 'shipped';

    // Return
    public const STATE_PENDING  = 'pending';
    public const STATE_RETURNED = 'returned';

    // For sale
    public const STATE_NONE      = 'none';
    public const STATE_PARTIAL   = 'partial';
    public const STATE_COMPLETED = 'completed';


    /**
     * Returns all the states.
     *
     * @return array<int, string>
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
     */
    public static function isValidState(Shipment|Subject|string $state): bool
    {
        return in_array(self::stateFromShipment($state), self::getStates(), true);
    }

    /**
     * Returns the notifiable states.
     *
     * @return array<int, string>
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
     */
    public static function isNotifiableState(Shipment|Subject|string $state): bool
    {
        return in_array(self::stateFromShipment($state), self::getNotifiableStates(), true);
    }

    /**
     * Returns the deletable states.
     *
     * @return array<int, string>
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
     */
    public static function isDeletableState(Shipment|Subject|string $state): bool
    {
        $state = self::stateFromShipment($state);

        return in_array($state, self::getDeletableStates(), true);
    }

    /**
     * Returns the preparable states.
     *
     * @return array<int, string>
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
     */
    public static function isPreparableState(Shipment|Subject|string $state): bool
    {
        $state = self::stateFromShipment($state);

        return in_array($state, self::getPreparableStates(), true);
    }

    /**
     * Returns whether the state has changed
     * from a non-deletable state to a deletable state.
     *
     * @param array $cs The persistence change set
     *
     * @return bool
     */
    public static function hasChangedToDeletable(array $cs): bool
    {
        return self::hasChangedTo($cs, self::getDeletableStates());
    }

    /**
     * Returns whether the state has changed
     * from a deletable state to a non-deletable state.
     *
     * @param array $cs The persistence change set
     *
     * @return bool
     */
    public static function hasChangedFromDeletable(array $cs): bool
    {
        return self::hasChangedFrom($cs, self::getDeletableStates());
    }

    /**
     * Returns the stockable states.
     *
     * @param bool $withPreparation
     *
     * @return array<int, string>
     */
    public static function getStockableStates(bool $withPreparation): array
    {
        $states = [
            self::STATE_READY,
            self::STATE_SHIPPED,
            self::STATE_RETURNED,
            // Sale states
            self::STATE_PARTIAL,
            self::STATE_COMPLETED,
        ];

        if ($withPreparation) {
            $states[] = self::STATE_PREPARATION;
        }

        return $states;
    }

    /**
     * Returns whether the given state is a stockable state.
     */
    public static function isStockableState(Shipment|Subject|string $state, bool $withPreparation): bool
    {
        $state = self::stateFromShipment($state);

        return in_array($state, self::getStockableStates($withPreparation), true);
    }

    /**
     * Returns whether the state has changed
     * from a non stockable state to a stockable state.
     *
     * @param array $cs The persistence change set
     * @param bool  $withPreparation
     *
     * @return bool
     */
    public static function hasChangedToStockable(array $cs, bool $withPreparation): bool
    {
        return self::hasChangedTo($cs, self::getStockableStates($withPreparation));
    }

    /**
     * Returns whether the state has changed
     * from a stockable state to a non-stockable state.
     *
     * @param array $cs The persistence change set
     * @param bool  $withPreparation
     *
     * @return bool
     */
    public static function hasChangedFromStockable(array $cs, bool $withPreparation): bool
    {
        return self::hasChangedFrom($cs, self::getStockableStates($withPreparation));
    }

    /**
     * Returns whether the state has changed
     * from a non preparation state to the preparation state.
     *
     * @param array $cs            The persistence change set
     * @param bool  $fromStockable Whether to check if it changed from a stockable state
     *
     * @return bool
     */
    public static function hasChangedToPreparation(array $cs, bool $fromStockable): bool
    {
        if (self::hasChangedTo($cs, [self::STATE_PREPARATION])) {
            if ($fromStockable) {
                return self::isStockableState($cs[0], false);
            }

            return true;
        }

        return false;
    }

    /**
     * Returns whether the state has changed
     * from the preparation state to a non preparation state.
     *
     * @param array $cs          The persistence change set
     * @param bool  $toStockable Whether to check if it changed to a stockable state
     *
     * @return bool
     */
    public static function hasChangedFromPreparation(array $cs, bool $toStockable): bool
    {
        if (self::hasChangedFrom($cs, [self::STATE_PREPARATION])) {
            if ($toStockable) {
                return self::isStockableState($cs[1], false);
            }

            return true;
        }

        return false;
    }

    /**
     * Returns whether the state has changed
     * to any of the given states, from any other state.
     *
     * @param array $cs     The persistence change set
     * @param array $states The states to check
     *
     * @return bool
     */
    private static function hasChangedTo(array $cs, array $states): bool
    {
        return self::assertValidChangeSet($cs)
            && !in_array($cs[0], $states, true)
            && in_array($cs[1], $states, true);
    }

    /**
     * Returns whether the state has changed
     * from any of the given states, to any other states.
     *
     * @param array $cs     The persistence change set
     * @param array $states The states to check
     *
     * @return bool
     */
    private static function hasChangedFrom(array $cs, array $states): bool
    {
        return self::assertValidChangeSet($cs)
            && in_array($cs[0], $states, true)
            && !in_array($cs[1], $states, true);
    }

    /**
     * Returns the shipment state.
     *
     * @param Shipment|Subject|string $stateOrShipment
     *
     * @return string
     */
    private static function stateFromShipment(Shipment|Subject|string $stateOrShipment): string
    {
        if ($stateOrShipment instanceof Shipment) {
            $stateOrShipment = $stateOrShipment->getState();
        } elseif ($stateOrShipment instanceof Subject) {
            $stateOrShipment = $stateOrShipment->getShipmentState();
        }

        if (!empty($stateOrShipment)) {
            return $stateOrShipment;
        }

        throw new InvalidArgumentException('Unexpected shipment state');
    }

    /**
     * Returns whether the change set is valid.
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
            array_key_exists(0, $cs)
            && array_key_exists(1, $cs)
            && (is_null($cs[0]) || self::isValidState($cs[0]))
            && (is_null($cs[1]) || self::isValidState($cs[1]))
        ) {
            return true;
        }

        throw new InvalidArgumentException('Unexpected shipment state change set.');
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
