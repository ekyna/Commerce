<?php

namespace Ekyna\Component\Commerce\Payment\Model;

use DateTime;
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
    const TRANSITION_CANCEL    = 'cancel';
    const TRANSITION_HANG      = 'hang';
    const TRANSITION_AUTHORIZE = 'authorize';
    const TRANSITION_ACCEPT    = 'accept';
    const TRANSITION_REJECT    = 'reject';
    const TRANSITION_REFUND    = 'refund';


    /**
     * Returns the payum request class for the given transition.
     *
     * @param string $transition
     *
     * @return string
     */
    static public function getRequestClass(string $transition): string
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
     *
     * @return array
     *
     * @see \Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentHelper::getTransitions
     */
    static function getAvailableTransitions(PaymentInterface $payment, bool $admin = false): array
    {
        $transitions = [];

        $method = $payment->getMethod();
        $state  = $payment->getState();

        if (!$admin) {
            if (in_array($state, [PaymentStates::STATE_NEW, PaymentStates::STATE_PENDING], true)) {
                $transitions[] = static::TRANSITION_CANCEL;
            }

            return $transitions;
        }

        if ($method->isManual()) {
            $transitions = [
                PaymentStates::STATE_CANCELED   => static::TRANSITION_CANCEL,
                PaymentStates::STATE_PENDING    => static::TRANSITION_HANG,
                PaymentStates::STATE_AUTHORIZED => static::TRANSITION_AUTHORIZE,
                PaymentStates::STATE_CAPTURED   => static::TRANSITION_ACCEPT,
                PaymentStates::STATE_FAILED     => static::TRANSITION_REJECT,
            ];
            unset($transitions[$state]);

            return $transitions;
        }

        if ($method->isCredit()) {
            if ($state !== PaymentStates::STATE_CANCELED) {
                $transitions[] = static::TRANSITION_CANCEL;
            }
            if ($state !== PaymentStates::STATE_CAPTURED) {
                $transitions[] = static::TRANSITION_ACCEPT;
            }

            return $transitions;
        }

        if ($method->isOutstanding()) {
            if ($state !== PaymentStates::STATE_CANCELED) {
                $transitions[] = static::TRANSITION_CANCEL;
            }
            if ($state !== PaymentStates::STATE_CAPTURED) {
                $transitions[] = static::TRANSITION_ACCEPT;
            }

            return $transitions;
        }

        if (!$payment->isRefund() && ($state === PaymentStates::STATE_CAPTURED)) {
            $transitions[] = static::TRANSITION_REFUND;
        }
        if (in_array($state, [PaymentStates::STATE_NEW, PaymentStates::STATE_PENDING], true)) {
            $transitions[] = static::TRANSITION_CANCEL;
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
