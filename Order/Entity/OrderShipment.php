<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentItemInterface;
use Ekyna\Component\Commerce\Shipment\Entity\AbstractShipment;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentLabelInterface;

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
     * @var OrderInvoiceInterface
     */
    protected $invoice;


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
            if ($previous = $this->order) {
                $this->order = null;
                $previous->removeShipment($this);
            }

            if ($this->order = $order) {
                $this->order->addShipment($this);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @inheritDoc
     */
    public function setInvoice(InvoiceInterface $invoice = null)
    {
        if ($invoice && !$invoice instanceof OrderInvoiceInterface) {
            throw new InvalidArgumentException("Expected instance of " . OrderInvoiceInterface::class);
        }

        if ($invoice !== $this->invoice) {
            if ($previous = $this->invoice) {
                $this->invoice = null;
                $previous->setShipment(null);
            }

            if ($this->invoice = $invoice) {
                $this->invoice->setShipment($this);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addItem(Shipment\ShipmentItemInterface $item)
    {
        if (!$item instanceof OrderShipmentItemInterface) {
            throw new InvalidArgumentException("Expected instance of " . OrderShipmentItemInterface::class);
        }

        return parent::addItem($item);
    }

    /**
     * @inheritDoc
     */
    public function removeItem(Shipment\ShipmentItemInterface $item)
    {
        if (!$item instanceof OrderShipmentItemInterface) {
            throw new InvalidArgumentException("Expected instance of " . OrderShipmentItemInterface::class);
        }

        return parent::removeItem($item);
    }

    /**
     * @inheritDoc
     */
    public function addParcel(Shipment\ShipmentParcelInterface $parcel)
    {
        if (!$parcel instanceof OrderShipmentParcel) {
            throw new InvalidArgumentException("Expected instance of " . OrderShipmentParcel::class);
        }

        return parent::addParcel($parcel);
    }

    /**
     * @inheritDoc
     */
    public function removeParcel(Shipment\ShipmentParcelInterface $parcel)
    {
        if (!$parcel instanceof OrderShipmentParcel) {
            throw new InvalidArgumentException("Expected instance of " . OrderShipmentParcel::class);
        }

        return parent::removeParcel($parcel);
    }

    /**
     * @inheritDoc
     */
    public function addLabel(ShipmentLabelInterface $label)
    {
        if (!$label instanceof OrderShipmentLabel) {
            throw new InvalidArgumentException("Expected instance of " . OrderShipmentLabel::class);
        }

        return parent::addLabel($label);
    }

    /**
     * @inheritDoc
     */
    public function removeLabel(ShipmentLabelInterface $label)
    {
        if (!$label instanceof OrderShipmentLabel) {
            throw new InvalidArgumentException("Expected instance of " . OrderShipmentLabel::class);
        }

        return parent::removeLabel($label);
    }

    /**
     * @inheritDoc
     */
    public function getLocale(): ?string
    {
        if ($this->order) {
            return $this->order->getLocale();
        }

        return null;
    }
}
