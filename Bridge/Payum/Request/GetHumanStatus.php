<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\Request;

use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Payum\Core\Request\BaseGetStatus;

/**
 * Class GetHumanStatus
 * @package Ekyna\Component\Commerce\Bridge\Payum\Request
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class GetHumanStatus extends BaseGetStatus
{
    /**
     * @inheritDoc
     */
    public function markNew()
    {
        $this->status = PaymentStates::STATE_NEW;
    }

    /**
     * @inheritDoc
     */
    public function isNew()
    {
        return $this->status === PaymentStates::STATE_NEW;
    }

    /**
     * @inheritDoc
     */
    public function markPending()
    {
        $this->status = PaymentStates::STATE_PENDING;
    }

    /**
     * @inheritDoc
     */
    public function isPending()
    {
        return $this->status === PaymentStates::STATE_PENDING;
    }

    /**
     * @inheritDoc
     */
    public function markSuspended()
    {
        $this->status = PaymentStates::STATE_SUSPENDED;
    }

    /**
     * @inheritDoc
     */
    public function isSuspended()
    {
        return $this->status === PaymentStates::STATE_SUSPENDED;
    }

    /**
     * @inheritDoc
     */
    public function markExpired()
    {
        $this->status = PaymentStates::STATE_EXPIRED;
    }

    /**
     * @inheritDoc
     */
    public function isExpired()
    {
        return $this->status === PaymentStates::STATE_EXPIRED;
    }

    /**
     * @inheritDoc
     */
    public function markCanceled()
    {
        $this->status = PaymentStates::STATE_CANCELED;
    }

    /**
     * @inheritDoc
     */
    public function isCanceled()
    {
        return $this->status === PaymentStates::STATE_CANCELED;
    }

    /**
     * @inheritDoc
     */
    public function markFailed()
    {
        $this->status = PaymentStates::STATE_FAILED;
    }

    /**
     * @inheritDoc
     */
    public function isFailed()
    {
        return $this->status === PaymentStates::STATE_FAILED;
    }

    /**
     * @inheritDoc
     */
    public function markCaptured()
    {
        $this->status = PaymentStates::STATE_CAPTURED;
    }

    /**
     * @inheritDoc
     */
    public function isCaptured()
    {
        return $this->status === PaymentStates::STATE_CAPTURED;
    }

    /**
     * @inheritDoc
     */
    public function isAuthorized()
    {
        return $this->status === PaymentStates::STATE_AUTHORIZED;
    }

    /**
     * @inheritDoc
     */
    public function markAuthorized()
    {
        $this->status = PaymentStates::STATE_AUTHORIZED;
    }

    /**
     * @inheritDoc
     */
    public function isPayedout()
    {
        return $this->status === PaymentStates::STATE_PAYEDOUT;
    }

    /**
     * @inheritDoc
     */
    public function markPayedout()
    {
        $this->status = PaymentStates::STATE_PAYEDOUT;
    }

    /**
     * @inheritDoc
     */
    public function isRefunded()
    {
        return $this->status === PaymentStates::STATE_REFUNDED;
    }

    /**
     * @inheritDoc
     */
    public function markRefunded()
    {
        $this->status = PaymentStates::STATE_REFUNDED;
    }

    /**
     * @inheritDoc
     */
    public function markUnknown()
    {
        $this->status = PaymentStates::STATE_UNKNOWN;
    }

    /**
     * @inheritDoc
     */
    public function isUnknown()
    {
        return $this->status === PaymentStates::STATE_UNKNOWN;
    }
}
