<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Interface OrderInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderInterface extends SaleInterface
{
    /**
     * Returns the key.
     *
     * @return string
     */
    public function getKey();

    /**
     * Sets the key.
     *
     * @param string $key
     *
     * @return $this|OrderInterface
     */
    public function setKey($key);

    /**
     * Returns the reference.
     *
     * @return string
     */
    public function getNumber();

    /**
     * Sets the reference.
     *
     * @param string $reference
     *
     * @return $this|OrderInterface
     */
    public function setNumber($reference);

    /**
     * Returns the state.
     *
     * @return string
     */
    public function getState();

    /**
     * Sets the state.
     *
     * @param string $state
     * @return $this|OrderInterface
     */
    public function setState($state);

    /**
     * Returns the payment state.
     *
     * @return string
     */
    public function getPaymentState();

    /**
     * Sets the payment state.
     *
     * @param string $paymentState
     * @return $this|OrderInterface
     */
    public function setPaymentState($paymentState);

    /**
     * Returns the shipment state.
     *
     * @return string
     */
    public function getShipmentState();

    /**
     * Sets the shipment state.
     *
     * @param string $shipmentState
     * @return $this|OrderInterface
     */
    public function setShipmentState($shipmentState);

    /**
     * Returns the paid total.
     *
     * @return float
     */
    public function getPaidTotal();

    /**
     * Sets the paid total.
     *
     * @param float $paidTotal
     *
     * @return $this|OrderInterface
     */
    public function setPaidTotal($paidTotal);

    /**
     * Returns whether the order has payments or not.
     *
     * @return bool
     */
    public function hasPayments();

    /**
     * Returns the payments.
     *
     * @return ArrayCollection|PaymentInterface[]
     */
    public function getPayments();

    /**
     * Returns whether the order has the payment or not.
     *
     * @param PaymentInterface $payment
     * @return bool
     */
    public function hasPayment(PaymentInterface $payment);

    /**
     * Adds the payment.
     *
     * @param PaymentInterface $payment
     * @return $this|OrderInterface
     */
    public function addPayment(PaymentInterface $payment);

    /**
     * Removes the payment.
     *
     * @param PaymentInterface $payment
     * @return $this|OrderInterface
     */
    public function removePayment(PaymentInterface $payment);

    /**
     * Returns whether the order has shipments or not.
     *
     * @return bool
     */
    public function hasShipments();

    /**
     * Returns the shipments.
     *
     * @return ArrayCollection|ShipmentInterface[]
     */
    public function getShipments();

    /**
     * Returns whether the order has the shipment or not.
     *
     * @param ShipmentInterface $shipment
     * @return bool
     */
    public function hasShipment(ShipmentInterface $shipment);

    /**
     * Adds the shipment.
     *
     * @param ShipmentInterface $shipment
     * @return $this|OrderInterface
     */
    public function addShipment(ShipmentInterface $shipment);

    /**
     * Removes the shipment.
     *
     * @param ShipmentInterface $shipment
     * @return $this|OrderInterface
     */
    public function removeShipment(ShipmentInterface $shipment);

    /**
     * Returns the "completed at" datetime.
     *
     * @return \DateTime
     */
    public function getCompletedAt();

    /**
     * Sets the "completed at" datetime.
     *
     * @param \DateTime $completedAt
     *
     * @return $this|OrderInterface
     */
    public function setCompletedAt(\DateTime $completedAt = null);
}
