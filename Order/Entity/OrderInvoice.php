<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Invoice\Entity\AbstractInvoice;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
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
    public function getSale(): ?SaleInterface
    {
        return $this->getOrder();
    }

    /**
     * @inheritdoc
     */
    public function setSale(SaleInterface $sale = null): DocumentInterface
    {
        if ($sale && !$sale instanceof Model\OrderInterface) {
            throw new UnexpectedValueException("Expected instance of " . Model\OrderInterface::class);
        }

        return $this->setOrder($sale);
    }

    /**
     * @inheritdoc
     */
    public function getOrder(): ?Model\OrderInterface
    {
        return $this->order;
    }

    /**
     * @inheritdoc
     */
    public function setOrder(Model\OrderInterface $order = null): Model\OrderInvoiceInterface
    {
        if ($order !== $this->order) {
            if ($previous = $this->order) {
                $this->order = null;
                $previous->removeInvoice($this);
            }

            if ($this->order = $order) {
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
    public function getShipment(): ?ShipmentInterface
    {
        return $this->shipment;
    }

    /**
     * @inheritdoc
     */
    public function setShipment(ShipmentInterface $shipment = null): InvoiceInterface
    {
        if ($shipment && !$shipment instanceof Model\OrderShipmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OrderShipmentInterface::class);
        }

        if ($this->shipment !== $shipment) {
            if ($previous = $this->shipment) {
                $this->shipment = null;
                $previous->setInvoice(null);
            }

            if ($this->shipment = $shipment) {
                $this->shipment->setInvoice($this);
            }
        }

        return $this;
    }
}
