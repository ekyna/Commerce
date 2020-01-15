<?php

namespace Ekyna\Component\Commerce\Payment\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class PaymentStates
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PaymentStates
{
    const STATE_NEW        = 'new';
    const STATE_PENDING    = 'pending';
    const STATE_CAPTURED   = 'captured';
    const STATE_FAILED     = 'failed';
    const STATE_CANCELED   = 'canceled';
    const STATE_REFUNDED   = 'refunded';
    const STATE_AUTHORIZED = 'authorized';
    const STATE_SUSPENDED  = 'suspended';
    const STATE_EXPIRED    = 'expired';
    const STATE_UNKNOWN    = 'unknown';

    // For sale
    const STATE_OUTSTANDING = 'outstanding';
    const STATE_DEPOSIT     = 'deposit';
    const STATE_COMPLETED   = 'completed';


    /**
     * Returns all the states.
     *
     * @return array
     */
    static public function getStates(): array
    {
        return [
            static::STATE_NEW,
            static::STATE_PENDING,
            static::STATE_CAPTURED,
            static::STATE_FAILED,
            static::STATE_CANCELED,
            static::STATE_REFUNDED,
            static::STATE_AUTHORIZED,
            static::STATE_SUSPENDED,
            static::STATE_EXPIRED,
            static::STATE_UNKNOWN,
            static::STATE_OUTSTANDING,
            static::STATE_DEPOSIT,
            static::STATE_COMPLETED,
        ];
    }

    /**
     * Returns whether or not the given state is valid.
     *
     * @param string $state
     * @param bool   $throwException
     *
     * @return bool
     */
    static public function isValidState(string $state, bool $throwException = true): bool
    {
        if (in_array($state, static::getStates(), true)) {
            return true;
        }

        if ($throwException) {
            throw new InvalidArgumentException("Invalid payment states '$state'.");
        }

        return false;
    }

    /**
     * Returns the notifiable states.
     *
     * @return array
     */
    static public function getNotifiableStates(): array
    {
        return [
            static::STATE_PENDING,
            static::STATE_CAPTURED,
            static::STATE_AUTHORIZED,
            static::STATE_FAILED,
            static::STATE_REFUNDED,
        ];
    }

    /**
     * Returns whether or not the given state is a notifiable state.
     *
     * @param PaymentInterface|string $state
     *
     * @return bool
     */
    static public function isNotifiableState($state): bool
    {
        $state = static::stateFromPayment($state);

        return in_array($state, static::getNotifiableStates(), true);
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
            static::STATE_FAILED,
        ];
    }

    /**
     * Returns whether or not the given state is a deletable state.
     *
     * @param PaymentInterface|string $state
     *
     * @return bool
     */
    static public function isDeletableState($state): bool
    {
        $state = static::stateFromPayment($state);

        return is_null($state) || in_array($state, static::getDeletableStates(), true);
    }

    /**
     * Returns the paid states.
     *
     * @param bool $andRefunded Whether to include refunded state.
     *
     * @return array
     */
    static public function getPaidStates(bool $andRefunded = false): array
    {
        if ($andRefunded) {
            return [
                static::STATE_CAPTURED,
                static::STATE_AUTHORIZED,
                static::STATE_REFUNDED,
            ];
        }

        return [
            static::STATE_CAPTURED,
            static::STATE_AUTHORIZED,
        ];
    }

    /**
     * Returns whether or not the given state is a paid state.
     *
     * @param PaymentInterface|string $state
     * @param bool                    $orRefunded
     *
     * @return bool
     */
    static public function isPaidState($state, bool $orRefunded = false): bool
    {
        return in_array(static::stateFromPayment($state), static::getPaidStates($orRefunded), true);
    }

    /**
     * Returns the canceled states.
     *
     * @return array
     */
    static public function getCanceledStates(): array
    {
        return [
            static::STATE_CANCELED,
            static::STATE_FAILED,
            static::STATE_REFUNDED,
        ];
    }

    /**
     * Returns whether or not the given state is a canceled state.
     *
     * @param PaymentInterface|string $state
     *
     * @return bool
     */
    static public function isCanceledState($state): bool
    {
        return in_array(static::stateFromPayment($state), static::getCanceledStates(), true);
    }

    /**
     * Returns the state from the payment.
     *
     * @param PaymentInterface|string $stateOrPayment
     *
     * @return string
     */
    static private function stateFromPayment($stateOrPayment): string
    {
        if ($stateOrPayment instanceof PaymentInterface) {
            $stateOrPayment = $stateOrPayment->getState();
        }

        if (is_string($stateOrPayment) && !empty($stateOrPayment)) {
            return $stateOrPayment;
        }

        throw new InvalidArgumentException("Expected string or " . PaymentInterface::class);
    }

    /**
     * Returns whether or not the state has changed
     * from a non paid state to a paid state.
     *
     * @param array $cs The persistence change set
     *
     * @return bool
     */
    static public function hasChangedToPaid(array $cs): bool
    {
        return static::assertValidChangeSet($cs)
            && !static::isPaidState($cs[0])
            && static::isPaidState($cs[1]);
    }

    /**
     * Returns whether or not the state has changed
     * from a paid state to a non paid state.
     *
     * @param array $cs The persistence change set
     *
     * @return bool
     */
    static public function hasChangedFromPaid(array $cs): bool
    {
        return static::assertValidChangeSet($cs)
            && static::isPaidState($cs[0])
            && !static::isPaidState($cs[1]);
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
    static private function assertValidChangeSet(array $cs): bool
    {
        if (
            array_key_exists(0, $cs)
            && array_key_exists(1, $cs)
            && (is_null($cs[0]) || static::isValidState($cs[0], false))
            && (is_null($cs[1]) || static::isValidState($cs[1], false))
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
