<?php

declare(strict_types=1);

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
    protected ?PaymentMethodInterface $method = null;


    public function getMethod(): ?PaymentMethodInterface
    {
        return $this->method;
    }

    public function setMethod(?PaymentMethodInterface $method): PaymentMethodMention
    {
        if ($this->method === $method) {
            return $this;
        }

        if ($previous = $this->method) {
            $this->method = null;
            $previous->removeMention($this);
        }

        if ($this->method = $method) {
            $this->method->addMention($this);
        }

        return $this;
    }
}
