<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Entity\AbstractSale;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;

/**
 * Class Order
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Order extends AbstractSale implements Model\OrderInterface
{
    /**
     * @var string
     */
    protected $shipmentState;

    /**
     * @var ArrayCollection|Shipment\ShipmentInterface[]
     */
    protected $shipments;

    /**
     * @var \DateTime
     */
    protected $completedAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->state = Model\OrderStates::STATE_NEW;
        $this->shipmentState = Shipment\ShipmentStates::STATE_NONE;
        $this->shipments = new ArrayCollection();

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function setShipmentState($shipmentState)
    {
        $this->shipmentState = $shipmentState;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShipmentState()
    {
        return $this->shipmentState;
    }

    /**
     * @inheritdoc
     *
     * @return Model\OrderAddressInterface
     */
    public function getInvoiceAddress()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->invoiceAddress;
    }

    /**
     * @inheritdoc
     */
    public function setInvoiceAddress(Common\AddressInterface $address = null)
    {
        if (null !== $address & !$address instanceof Model\OrderAddressInterface) {
            throw new InvalidArgumentException('Expected instance of OrderAddressInterface.');
        }

        if ($address !== $current = $this->getInvoiceAddress()) {
            if (null !== $current) {
                $current->setInvoiceOrder(null);
            }

            $this->invoiceAddress = $address;

            if (null !== $address) {
                $address->setInvoiceOrder($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return Model\OrderAddressInterface
     */
    public function getDeliveryAddress()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->deliveryAddress;
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryAddress(Common\AddressInterface $address = null)
    {
        if (null !== $address && !$address instanceof Model\OrderAddressInterface) {
            throw new InvalidArgumentException('Expected instance of OrderAddressInterface.');
        }

        if ($address !== $current = $this->getDeliveryAddress()) {
            if (null !== $current) {
                $current->setDeliveryOrder(null);
            }

            $this->deliveryAddress = $address;

            if (null !== $address) {
                $address->setDeliveryOrder($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasAttachment(Common\SaleAttachmentInterface $attachment)
    {
        if (!$attachment instanceof Model\OrderAttachmentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderAttachmentInterface.");
        }

        return $this->attachments->contains($attachment);
    }

    /**
     * @inheritdoc
     */
    public function addAttachment(Common\SaleAttachmentInterface $attachment)
    {
        if (!$attachment instanceof Model\OrderAttachmentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderAttachmentInterface.");
        }

        if (!$this->hasAttachment($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setOrder($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAttachment(Common\SaleAttachmentInterface $attachment)
    {
        if (!$attachment instanceof Model\OrderAttachmentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderAttachmentInterface.");
        }

        if ($this->hasAttachment($attachment)) {
            $this->attachments->removeElement($attachment);
            //$attachment->setOrder(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasItem(Common\SaleItemInterface $item)
    {
        if (!$item instanceof Model\OrderItemInterface) {
            throw new InvalidArgumentException("Expected instance of OrderItemInterface.");
        }

        return $this->items->contains($item);
    }

    /**
     * @inheritdoc
     */
    public function addItem(Common\SaleItemInterface $item)
    {
        if (!$item instanceof Model\OrderItemInterface) {
            throw new InvalidArgumentException("Expected instance of OrderItemInterface.");
        }

        if (!$this->hasItem($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeItem(Common\SaleItemInterface $item)
    {
        if (!$item instanceof Model\OrderItemInterface) {
            throw new InvalidArgumentException("Expected instance of OrderItemInterface.");
        }

        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
            //$item->setOrder(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasAdjustment(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\OrderAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderAdjustmentInterface.");
        }

        return $this->adjustments->contains($adjustment);
    }

    /**
     * @inheritdoc
     */
    public function addAdjustment(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\OrderAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderAdjustmentInterface.");
        }

        if (!$this->hasAdjustment($adjustment)) {
            $this->adjustments->add($adjustment);
            $adjustment->setOrder($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAdjustment(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\OrderAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderAdjustmentInterface.");
        }

        if ($this->hasAdjustment($adjustment)) {
            $this->adjustments->removeElement($adjustment);
            //$adjustment->setOrder(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasPayment(Payment\PaymentInterface $payment)
    {
        if (!$payment instanceof Model\OrderPaymentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderPaymentInterface.");
        }

        return $this->payments->contains($payment);
    }

    /**
     * @inheritdoc
     */
    public function addPayment(Payment\PaymentInterface $payment)
    {
        if (!$payment instanceof Model\OrderPaymentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderPaymentInterface.");
        }

        if (!$this->hasPayment($payment)) {
            $this->payments->add($payment);
            $payment->setOrder($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removePayment(Payment\PaymentInterface $payment)
    {
        if (!$payment instanceof Model\OrderPaymentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderPaymentInterface.");
        }

        if ($this->hasPayment($payment)) {
            $this->payments->removeElement($payment);
            //$payment->setOrder(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasShipments()
    {
        return 0 < $this->shipments->count();
    }

    /**
     * @inheritdoc
     */
    public function getShipments()
    {
        return $this->shipments;
    }

    /**
     * @inheritdoc
     */
    public function hasShipment(Shipment\ShipmentInterface $shipment)
    {
        if (!$shipment instanceof Model\OrderShipmentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderShipmentInterface.");
        }

        return $this->shipments->contains($shipment);
    }

    /**
     * @inheritdoc
     */
    public function addShipment(Shipment\ShipmentInterface $shipment)
    {
        if (!$shipment instanceof Model\OrderShipmentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderShipmentInterface.");
        }

        if (!$this->hasShipment($shipment)) {
            $this->shipments->add($shipment);
            $shipment->setOrder($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeShipment(Shipment\ShipmentInterface $shipment)
    {
        if (!$shipment instanceof Model\OrderShipmentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderShipmentInterface.");
        }

        if ($this->hasShipment($shipment)) {
            $this->shipments->removeElement($shipment);
            //$shipment->setOrder(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCompletedAt()
    {
        return $this->completedAt;
    }

    /**
     * @inheritdoc
     */
    public function setCompletedAt(\DateTime $completedAt = null)
    {
        $this->completedAt = $completedAt;

        return $this;
    }
}
