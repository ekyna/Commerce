<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
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
    protected ?OrderInterface        $order   = null;
    protected ?OrderInvoiceInterface $invoice = null;


    public function getSale(): ?SaleInterface
    {
        return $this->getOrder();
    }

    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    public function setOrder(?OrderInterface $order): OrderShipmentInterface
    {
        if ($order === $this->order) {
            return $this;
        }

        if ($previous = $this->order) {
            $this->order = null;
            $previous->removeShipment($this);
        }

        if ($this->order = $order) {
            $this->order->addShipment($this);
        }

        return $this;
    }

    public function getInvoice(): ?InvoiceInterface
    {
        return $this->invoice;
    }

    public function setInvoice(?InvoiceInterface $invoice): Shipment\ShipmentInterface
    {
        if ($invoice && !$invoice instanceof OrderInvoiceInterface) {
            throw new UnexpectedTypeException($invoice, OrderInvoiceInterface::class);
        }

        if ($invoice === $this->invoice) {
            return $this;
        }

        if ($previous = $this->invoice) {
            $this->invoice = null;
            $previous->setShipment(null);
        }

        if ($this->invoice = $invoice) {
            $this->invoice->setShipment($this);
        }

        return $this;
    }

    public function addItem(Shipment\ShipmentItemInterface $item): Shipment\ShipmentInterface
    {
        if (!$item instanceof OrderShipmentItemInterface) {
            throw new UnexpectedTypeException($item, OrderShipmentItemInterface::class);
        }

        return parent::addItem($item);
    }

    public function removeItem(Shipment\ShipmentItemInterface $item): Shipment\ShipmentInterface
    {
        if (!$item instanceof OrderShipmentItemInterface) {
            throw new UnexpectedTypeException($item, OrderShipmentItemInterface::class);
        }

        return parent::removeItem($item);
    }

    public function addParcel(Shipment\ShipmentParcelInterface $parcel): Shipment\ShipmentInterface
    {
        if (!$parcel instanceof OrderShipmentParcel) {
            throw new UnexpectedTypeException($parcel, OrderShipmentParcel::class);
        }

        return parent::addParcel($parcel);
    }

    public function removeParcel(Shipment\ShipmentParcelInterface $parcel): Shipment\ShipmentInterface
    {
        if (!$parcel instanceof OrderShipmentParcel) {
            throw new UnexpectedTypeException($parcel, OrderShipmentParcel::class);
        }

        return parent::removeParcel($parcel);
    }

    public function addLabel(ShipmentLabelInterface $label): Shipment\ShipmentDataInterface
    {
        if (!$label instanceof OrderShipmentLabel) {
            throw new UnexpectedTypeException($label, OrderShipmentLabel::class);
        }

        return parent::addLabel($label);
    }

    public function removeLabel(ShipmentLabelInterface $label): Shipment\ShipmentDataInterface
    {
        if (!$label instanceof OrderShipmentLabel) {
            throw new UnexpectedTypeException($label, OrderShipmentLabel::class);
        }

        return parent::removeLabel($label);
    }

    public function getLocale(): ?string
    {
        if ($this->order) {
            return $this->order->getLocale();
        }

        return null;
    }
}
