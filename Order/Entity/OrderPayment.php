<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Order\Model;
use Ekyna\Component\Commerce\Payment\Entity\AbstractPayment;

/**
 * Class OrderPayment
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderPayment extends AbstractPayment implements Model\OrderPaymentInterface
{
    protected ?Model\OrderInterface $order = null;


    public function getSale(): ?SaleInterface
    {
        return $this->getOrder();
    }

    public function getOrder(): ?Model\OrderInterface
    {
        return $this->order;
    }

    public function setOrder(Model\OrderInterface $order = null): Model\OrderPaymentInterface
    {
        if ($order === $this->order) {
            return $this;
        }

        if ($previous = $this->order) {
            $this->order = null;
            $previous->removePayment($this);
        }

        if ($this->order = $order) {
            $this->order->addPayment($this);
        }

        return $this;
    }

    public function getLocale(): ?string
    {
        if ($this->order) {
            return $this->order->getLocale();
        }

        return null;
    }
}
