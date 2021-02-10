<?php

namespace Ekyna\Component\Commerce\Payment\Model;

use Ekyna\Component\Commerce\Bridge\Payum\Request as Commerce;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Payum\Core\Request as Payum;

/**
 * Class PaymentTransitions
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PaymentTransitions
{
    public const TRANSITION_CANCEL    = 'cancel';
    public const TRANSITION_HANG      = 'hang';
    public const TRANSITION_AUTHORIZE = 'authorize';
    public const TRANSITION_ACCEPT    = 'accept';
    public const TRANSITION_PAYOUT    = 'payout';
    public const TRANSITION_REJECT    = 'reject';
    public const TRANSITION_REFUND    = 'refund';


    /**
     * Returns the payum request class for the given transition.
     *
     * @param string $transition
     *
     * @return string
     */
    public static function getRequestClass(string $transition): string
    {
        switch ($transition) {
            case self::TRANSITION_CANCEL:
                return Payum\Cancel::class;
            case self::TRANSITION_HANG:
                return Commerce\Hang::class;
            case self::TRANSITION_AUTHORIZE:
                return Payum\Authorize::class;
            case self::TRANSITION_ACCEPT:
                return Commerce\Accept::class;
            case self::TRANSITION_PAYOUT:
                return Payum\Payout::class;
            case self::TRANSITION_REJECT:
                return Commerce\Reject::class;
            case self::TRANSITION_REFUND:
                return Payum\Refund::class;
        }

        throw new InvalidArgumentException("Unexpected payment transition");
    }

    /**
     * Returns the available payment transitions.
     *
     * @param PaymentInterface $payment
     * @param bool             $admin
     * @param bool             $locked
     *
     * @return string[]
     *
     * @see \Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentHelper::getTransitions
     */
    public static function getAvailableTransitions(
        PaymentInterface $payment,
        bool $admin = false,
        bool $locked = false
    ): array {
        $method = $payment->getMethod();
        $state = $payment->getState();

        // Customer
        if (!$admin) {
            if (in_array($state, [PaymentStates::STATE_NEW, PaymentStates::STATE_PENDING], true)) {
                return [self::TRANSITION_CANCEL];
            }

            return [];
        }

        // Administrator
        $transitions = [];

        if ($method->isManual()) {
            if ($locked) {
                $transitions = [
                    PaymentStates::STATE_AUTHORIZED => self::TRANSITION_AUTHORIZE,
                    PaymentStates::STATE_PAYEDOUT   => self::TRANSITION_PAYOUT,
                    PaymentStates::STATE_CAPTURED   => self::TRANSITION_ACCEPT,
                ];
            } else {
                $transitions = [
                    PaymentStates::STATE_CANCELED   => self::TRANSITION_CANCEL,
                    PaymentStates::STATE_PENDING    => self::TRANSITION_HANG,
                    PaymentStates::STATE_AUTHORIZED => self::TRANSITION_AUTHORIZE,
                    PaymentStates::STATE_PAYEDOUT   => self::TRANSITION_PAYOUT,
                    PaymentStates::STATE_CAPTURED   => self::TRANSITION_ACCEPT,
                    PaymentStates::STATE_FAILED     => self::TRANSITION_REJECT,
                ];
            }
            unset($transitions[$state]);

            return $transitions;
        }

        if ($method->isCredit() || $method->isOutstanding()) {
            if (!$locked && ($state !== PaymentStates::STATE_CANCELED)) {
                $transitions[] = self::TRANSITION_CANCEL;
            }
            if ($state !== PaymentStates::STATE_CAPTURED) {
                $transitions[] = self::TRANSITION_ACCEPT;
            }

            return $transitions;
        }

        if (!$locked && !$payment->isRefund() && ($state === PaymentStates::STATE_CAPTURED)) {
            $transitions[] = self::TRANSITION_REFUND;
        }

        if (in_array($state, [PaymentStates::STATE_NEW, PaymentStates::STATE_PENDING], true)) {
            $transitions[] = self::TRANSITION_CANCEL;
        }

        return $transitions;
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
