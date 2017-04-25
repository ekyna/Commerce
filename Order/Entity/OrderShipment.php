<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Entity\AbstractShipment;

/**
 * Class OrderShipment
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipment extends AbstractShipment implements OrderShipmentInterface
{
    /**
     * @var OrderInterface
     */
    protected $order;


    /**
     * @inheritDoc
     *
     * @return OrderInterface
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
    public function setOrder(OrderInterface $order = null)
    {
        if ($order !== $this->order) {
            $previous = $this->order;
            $this->order = $order;

            if ($previous) {
                $previous->removeShipment($this);
            }

            if ($this->order) {
                $this->order->addShipment($this);
            }
        }

        return $this;
    }
}
