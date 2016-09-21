<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Interface OrderInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderInterface extends
    Common\SaleInterface,
    Common\NumberSubjectInterface,
    Common\KeySubjectInterface
{
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
     * @return ArrayCollection|OrderPaymentInterface[]
     */
    public function getPayments();

    /**
     * Returns whether the order has the payment or not.
     *
     * @param OrderPaymentInterface $payment
     * @return bool
     */
    public function hasPayment(OrderPaymentInterface $payment);

    /**
     * Adds the payment.
     *
     * @param OrderPaymentInterface $payment
     * @return $this|OrderInterface
     */
    public function addPayment(OrderPaymentInterface $payment);

    /**
     * Removes the payment.
     *
     * @param OrderPaymentInterface $payment
     * @return $this|OrderInterface
     */
    public function removePayment(OrderPaymentInterface $payment);

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
