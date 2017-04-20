<?php

declare(strict_types=1);

namespace Acme\Product\Entity;

use Ekyna\Component\Commerce\Payment\Entity\PaymentMethod as BaseMethod;

/**
 * Class PaymentMethod
 * @package Acme\Product\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethod extends BaseMethod
{
    private bool $manual      = false;
    private bool $credit      = false;
    private bool $outstanding = false;
    private bool $factor      = false;

    public function isCredit(): bool
    {
        return $this->credit;
    }

    public function setCredit(bool $credit): PaymentMethod
    {
        $this->credit = $credit;

        return $this;
    }

    public function isManual(): bool
    {
        return $this->manual;
    }

    public function setManual(bool $manual): PaymentMethod
    {
        $this->manual = $manual;

        return $this;
    }

    public function isOutstanding(): bool
    {
        return $this->outstanding;
    }

    public function setOutstanding(bool $outstanding): PaymentMethod
    {
        $this->outstanding = $outstanding;

        return $this;
    }

    public function isFactor(): bool
    {
        return $this->factor;
    }

    public function setFactor(bool $factor): PaymentMethod
    {
        $this->factor = $factor;

        return $this;
    }
}
