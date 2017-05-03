<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Credit\Entity\AbstractCredit;
use Ekyna\Component\Commerce\Order\Model;

/**
 * Class OrderCredit
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderCredit extends AbstractCredit implements Model\OrderCreditInterface
{
    /**
     * @var Model\OrderInterface
     */
    protected $order;

    /**
     * @var Model\OrderShipmentInterface
     */
    protected $shipment;


    /**
     * @inheritDoc
     *
     * @return Model\OrderInterface
     */
    public function getSale()
    {
        return $this->getOrder();
    }

    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @inheritdoc
     */
    public function setOrder(Model\OrderInterface $order = null)
    {
        if ($order !== $this->order) {
            $previous = $this->order;
            $this->order = $order;

            if ($previous) {
                $previous->removeCredit($this);
            }

            if ($this->order) {
                $this->order->addCredit($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return Model\OrderShipmentInterface
     */
    public function getShipment()
    {
        return $this->shipment;
    }

    /**
     * @inheritdoc
     */
    public function setShipment(Model\OrderShipmentInterface $shipment = null)
    {
        $this->shipment = $shipment;

        return $this;
    }
}
