<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Entity\AbstractInvoice;
use Ekyna\Component\Commerce\Order\Model;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

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
     * @inheritdoc
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
    public function setSale(SaleInterface $sale = null)
    {
        return $this->setOrder($sale);
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
    public function setShipment(ShipmentInterface $shipment = null)
    {
        if ($shipment && !$shipment instanceof Model\OrderShipmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderShipmentInterface::class);
        }

        if ($this->shipment !== $shipment) {
            $previous = $this->shipment;
            $this->shipment = $shipment;

            if (null !== $previous) {
                $this->shipment->setInvoice(null);
            }

            if (null !== $shipment) {
                $shipment->setInvoice($this);
            }
        }

        return $this;
    }
}
