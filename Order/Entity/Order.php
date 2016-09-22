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
        $this->shipmentState = Shipment\ShipmentStates::STATE_PENDING;
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
            $item->setOrder($this);
            $this->items->add($item);
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
            $item->setOrder(null);
            $this->items->removeElement($item);
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
            $adjustment->setOrder($this);
            $this->adjustments->add($adjustment);
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
            $adjustment->setOrder(null);
            $this->adjustments->removeElement($adjustment);
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
            $payment->setOrder($this);
            $this->payments->add($payment);
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
            $payment->setOrder(null);
            $this->payments->removeElement($payment);
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
    public function hasShipment(Model\OrderShipmentInterface $shipment)
    {
        return $this->shipments->contains($shipment);
    }

    /**
     * @inheritdoc
     */
    public function addShipment(Model\OrderShipmentInterface $shipment)
    {
        if (!$this->hasShipment($shipment)) {
            $shipment->setOrder($this);
            $this->shipments->add($shipment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeShipment(Model\OrderShipmentInterface $shipment)
    {
        if ($this->hasShipment($shipment)) {
            $shipment->setOrder(null);
            $this->shipments->removeElement($shipment);
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

    /**
     * @inheritdoc
     */
    protected function validateAddressClass(Common\AddressInterface $address)
    {
        if (!$address instanceof Model\OrderAddressInterface) {
            throw new InvalidArgumentException('Expected instance of OrderAddressInterface.');
        }
    }
}
