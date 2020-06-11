<?php

namespace Ekyna\Component\Commerce\Payment\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractMention;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;

/**
 * Class PaymentMention
 * @package Ekyna\Component\Commerce\Payment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodMention extends AbstractMention
{
    /**
     * @var PaymentMethodInterface|null
     */
    protected $method;


    /**
     * Returns the method.
     *
     * @return PaymentMethodInterface|null
     */
    public function getMethod(): PaymentMethodInterface
    {
        return $this->method;
    }

    /**
     * Sets the method.
     *
     * @param PaymentMethodInterface|null $method
     *
     * @return PaymentMethodMention
     */
    public function setMethod(PaymentMethodInterface $method = null): PaymentMethodMention
    {
        if ($this->method !== $method) {
            if ($previous = $this->method) {
                $this->method = null;
                $previous->removeMention($this);
            }

            if ($this->method = $method) {
                $this->method->addMention($this);
            }
        }

        return $this;
    }
}
