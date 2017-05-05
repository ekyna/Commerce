<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Invoice\Entity\AbstractInvoice;
use Ekyna\Component\Commerce\Order\Model;

/**
 * Class OrderInvoice
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoice extends AbstractInvoice implements Model\OrderInvoiceInterface
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
                $previous->removeInvoice($this);
            }

            if ($this->order) {
                $this->order->addInvoice($this);
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
