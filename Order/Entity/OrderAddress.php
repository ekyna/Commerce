<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAddress;
use Ekyna\Component\Commerce\Order\Model;

/**
 * Class OrderAddress
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderAddress extends AbstractAddress implements Model\OrderAddressInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\OrderInterface
     */
    protected $invoiceOrder;

    /**
     * @var Model\OrderInterface
     */
    protected $deliveryOrder;


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getInvoiceOrder()
    {
        return $this->invoiceOrder;
    }

    /**
     * @inheritdoc
     */
    public function setInvoiceOrder(Model\OrderInterface $order = null)
    {
        if ($order !== $this->invoiceOrder) {
            if ($previous = $this->invoiceOrder) {
                $this->invoiceOrder = null;
                $previous->setInvoiceAddress(null);
            }

            if ($this->invoiceOrder = $order) {
                $this->invoiceOrder->setInvoiceAddress($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryOrder()
    {
        return $this->deliveryOrder;
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryOrder(Model\OrderInterface $order = null)
    {
        if ($order !== $this->deliveryOrder) {
            if ($previous = $this->deliveryOrder) {
                $this->deliveryOrder = null;
                $previous->setDeliveryAddress(null);
            }

            if ($this->deliveryOrder = $order) {
                $this->deliveryOrder->setDeliveryAddress($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        if (null !== $this->invoiceOrder) {
            return $this->invoiceOrder;
        } elseif (null !== $this->deliveryOrder) {
            return $this->deliveryOrder;
        }

        return null;
    }
}
