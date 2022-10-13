<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

use function array_key_exists;
use function in_array;
use function is_null;

/**
 * Class PaymentStates
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PaymentStates
{
    public const STATE_NEW        = 'new';
    public const STATE_PENDING    = 'pending';
    public const STATE_CAPTURED   = 'captured';
    public const STATE_FAILED     = 'failed';
    public const STATE_CANCELED   = 'canceled';
    public const STATE_REFUNDED   = 'refunded';
    public const STATE_AUTHORIZED = 'authorized';
    public const STATE_PAYEDOUT   = 'payedout';
    public const STATE_SUSPENDED  = 'suspended';
    public const STATE_EXPIRED    = 'expired';
    public const STATE_UNKNOWN    = 'unknown';

    // For sale
    public const STATE_OUTSTANDING = 'outstanding';
    public const STATE_DEPOSIT     = 'deposit';
    public const STATE_COMPLETED   = 'completed';


    /**
     * Returns all the states.
     *
     * @return array
     */
    public static function getStates(): array
    {
        return [
            self::STATE_NEW,
            self::STATE_PENDING,
            self::STATE_CAPTURED,
            self::STATE_FAILED,
            self::STATE_CANCELED,
            self::STATE_REFUNDED,
            self::STATE_AUTHORIZED,
            self::STATE_PAYEDOUT,
            self::STATE_SUSPENDED,
            self::STATE_EXPIRED,
            self::STATE_UNKNOWN,
            self::STATE_OUTSTANDING,
            self::STATE_DEPOSIT,
            self::STATE_COMPLETED,
        ];
    }

    /**
     * Returns whether the given state is valid.
     *
     * @param string $state
     * @param bool   $throwException
     *
     * @return bool
     */
    public static function isValidState(string $state, bool $throwException = true): bool
    {
        if (in_array($state, self::getStates(), true)) {
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
    public static function getNotifiableStates(): array
    {
        return [
            self::STATE_PENDING,
            self::STATE_AUTHORIZED,
            self::STATE_CAPTURED,
            self::STATE_PAYEDOUT,
            self::STATE_REFUNDED,
            self::STATE_FAILED,
        ];
    }

    /**
     * Returns whether the given state is a notifiable state.
     */
    public static function isNotifiableState(PaymentInterface|string $state): bool
    {
        $state = self::stateFromPayment($state);

        return in_array($state, self::getNotifiableStates(), true);
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
            self::STATE_CANCELED,
            self::STATE_FAILED,
        ];
    }

    /**
     * Returns whether the given state is a deletable state.
     */
    public static function isDeletableState(PaymentInterface|string $state): bool
    {
        $state = self::stateFromPayment($state);

        return in_array($state, self::getDeletableStates(), true);
    }

    /**
     * Returns the paid states.
     *
     * @param bool $andRefunded Whether to include refunded state.
     *
     * @return array<int, string>
     */
    public static function getPaidStates(bool $andRefunded = false): array
    {
        if ($andRefunded) {
            return [
                self::STATE_CAPTURED,
                self::STATE_AUTHORIZED,
                self::STATE_PAYEDOUT,
                self::STATE_REFUNDED,
            ];
        }

        return [
            self::STATE_CAPTURED,
            self::STATE_AUTHORIZED,
            self::STATE_PAYEDOUT,
        ];
    }

    /**
     * Returns whether the given state is a paid state.
     */
    public static function isPaidState(PaymentInterface|string $state, bool $orRefunded = false): bool
    {
        return in_array(self::stateFromPayment($state), self::getPaidStates($orRefunded), true);
    }

    /**
     * Returns the completed states.
     *
     * @param bool $andRefunded Whether to include refunded state.
     *
     * @return array<int, string>
     */
    public static function getCompletedStates(bool $andRefunded = false): array
    {
        if ($andRefunded) {
            return [
                self::STATE_CAPTURED,
                self::STATE_PAYEDOUT,
                self::STATE_REFUNDED,
            ];
        }

        return [
            self::STATE_CAPTURED,
            self::STATE_PAYEDOUT,
        ];
    }

    /**
     * Returns whether the given state is a completed state.
     */
    public static function isCompletedState(PaymentInterface|string $state, bool $orRefunded = false): bool
    {
        return in_array(self::stateFromPayment($state), self::getCompletedStates($orRefunded), true);
    }

    /**
     * Returns the canceled states.
     *
     * @return array<int, string>
     */
    public static function getCanceledStates(): array
    {
        return [
            self::STATE_CANCELED,
            self::STATE_FAILED,
            self::STATE_REFUNDED,
        ];
    }

    /**
     * Returns whether the given state is a canceled state.
     */
    public static function isCanceledState(PaymentInterface|string $state): bool
    {
        return in_array(self::stateFromPayment($state), self::getCanceledStates(), true);
    }

    /**
     * Returns the state from the payment.
     */
    private static function stateFromPayment(PaymentInterface|string $stateOrPayment): string
    {
        if ($stateOrPayment instanceof PaymentInterface) {
            $stateOrPayment = $stateOrPayment->getState();
        }

        if (!empty($stateOrPayment)) {
            return $stateOrPayment;
        }

        throw new InvalidArgumentException('Blank payment state.');
    }

    /**
     * Returns whether the state has changed
     * from a non paid state to a paid state.
     *
     * @param array $cs The persistence change set
     *
     * @return bool
     */
    public static function hasChangedToPaid(array $cs): bool
    {
        return self::assertValidChangeSet($cs)
            && !self::isPaidState($cs[0])
            && self::isPaidState($cs[1]);
    }

    /**
     * Returns whether the state has changed
     * from a paid state to a non paid state.
     *
     * @param array $cs The persistence change set
     *
     * @return bool
     */
    public static function hasChangedFromPaid(array $cs): bool
    {
        return self::assertValidChangeSet($cs)
            && self::isPaidState($cs[0])
            && !self::isPaidState($cs[1]);
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
            && (is_null($cs[0]) || self::isValidState($cs[0], false))
            && (is_null($cs[1]) || self::isValidState($cs[1], false))
        ) {
            return true;
        }

        throw new InvalidArgumentException('Unexpected payment state change set.');
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
