<?php

namespace Acme\Product\Entity;

use Ekyna\Component\Commerce\Payment\Entity\PaymentMethod as BaseMethod;

/**
 * Class PaymentMethod
 * @package Acme\Product\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethod extends BaseMethod
{
    /** @var bool */
    private $manual;

    /** @var bool */
    private $credit;

    /** @var bool */
    private $outstanding;

    /**
     * Returns the credit.
     *
     * @return bool
     */
    public function isCredit(): bool
    {
        return $this->credit;
    }

    /**
     * Sets the credit.
     *
     * @param bool $credit
     *
     * @return PaymentMethod
     */
    public function setCredit(bool $credit): PaymentMethod
    {
        $this->credit = $credit;

        return $this;
    }

    /**
     * Returns the manual.
     *
     * @return bool
     */
    public function isManual(): bool
    {
        return $this->manual;
    }

    /**
     * Sets the manual.
     *
     * @param bool $manual
     *
     * @return PaymentMethod
     */
    public function setManual(bool $manual): PaymentMethod
    {
        $this->manual = $manual;

        return $this;
    }

    /**
     * Returns the outstanding.
     *
     * @return bool
     */
    public function isOutstanding(): bool
    {
        return $this->outstanding;
    }

    /**
     * Sets the outstanding.
     *
     * @param bool $outstanding
     *
     * @return PaymentMethod
     */
    public function setOutstanding(bool $outstanding): PaymentMethod
    {
        $this->outstanding = $outstanding;

        return $this;
    }
}
