<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSaleAddress;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Order\Model;

/**
 * Class OrderAddress
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderAddress extends AbstractSaleAddress implements Model\OrderAddressInterface
{
    protected ?Model\OrderInterface $invoiceOrder  = null;
    protected ?Model\OrderInterface $deliveryOrder = null;
    protected ?Model\OrderInterface $destinationOrder = null;

    public function getInvoiceOrder(): ?Model\OrderInterface
    {
        return $this->invoiceOrder;
    }

    public function setInvoiceOrder(?Model\OrderInterface $order): Model\OrderAddressInterface
    {
        if ($order === $this->invoiceOrder) {
            return $this;
        }

        if ($previous = $this->invoiceOrder) {
            $this->invoiceOrder = null;
            $previous->setInvoiceAddress(null);
        }

        if ($this->invoiceOrder = $order) {
            $this->invoiceOrder->setInvoiceAddress($this);
        }

        return $this;
    }

    public function getDeliveryOrder(): ?Model\OrderInterface
    {
        return $this->deliveryOrder;
    }

    public function setDeliveryOrder(?Model\OrderInterface $order): Model\OrderAddressInterface
    {
        if ($order === $this->deliveryOrder) {
            return $this;
        }

        if ($previous = $this->deliveryOrder) {
            $this->deliveryOrder = null;
            $previous->setDeliveryAddress(null);
        }

        if ($this->deliveryOrder = $order) {
            $this->deliveryOrder->setDeliveryAddress($this);
        }

        return $this;
    }

    public function getDestinationOrder(): ?Model\OrderInterface
    {
        return $this->destinationOrder;
    }

    public function setDestinationOrder(?Model\OrderInterface $order): Model\OrderAddressInterface
    {
        if ($order === $this->destinationOrder) {
            return $this;
        }

        if ($previous = $this->destinationOrder) {
            $this->destinationOrder = null;
            $previous->setDestinationAddress(null);
        }

        if ($this->destinationOrder = $order) {
            $this->destinationOrder->setDestinationAddress($this);
        }

        return $this;
    }

    public function getOrder(): ?Model\OrderInterface
    {
        return $this->invoiceOrder ?: $this->deliveryOrder;
    }

    public function getSale(): ?SaleInterface
    {
        return $this->getOrder();
    }
}
