<?php

namespace Ekyna\Component\Commerce\Payment\Model;

use Ekyna\Component\Commerce\Common\Model\CurrencySubjectInterface;

/**
 * Interface PaymentSubjectInterface
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentSubjectInterface extends CurrencySubjectInterface
{
    /**
     * Returns the deposit total.
     *
     * @return float
     */
    public function getDepositTotal();

    /**
     * Sets the deposit total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setDepositTotal($total);

    /**
     * Returns the grand total.
     *
     * @return float
     */
    public function getGrandTotal();

    /**
     * Sets the grand total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setGrandTotal($total);

    /**
     * Returns the paid total.
     *
     * @return float
     */
    public function getPaidTotal();

    /**
     * Sets the paid total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setPaidTotal($total);

    /**
     * Returns the pending total.
     *
     * @return float
     */
    public function getPendingTotal();

    /**
     * Sets the pending total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setPendingTotal($total);

    /**
     * Returns the accepted outstanding total.
     *
     * @return float
     */
    public function getOutstandingAccepted();

    /**
     * Sets the accepted outstanding total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingAccepted($total);

    /**
     * Returns the expired outstanding total.
     *
     * @return float
     */
    public function getOutstandingExpired();

    /**
     * Sets the expired outstanding total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingExpired($total);

    /**
     * Returns the outstanding limit.
     *
     * @return float
     */
    public function getOutstandingLimit();

    /**
     * Sets the outstanding amount.
     *
     * @param float $amount
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingLimit($amount);

    /**
     * Returns the outstanding date.
     *
     * @return \DateTime
     */
    public function getOutstandingDate();

    /**
     * Sets the outstanding date.
     *
     * @param \DateTime $date
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingDate(\DateTime $date = null);

    /**
     * Returns the payment state.
     *
     * @return string
     */
    public function getPaymentState();

    /**
     * Sets the payment state.
     *
     * @param string $state
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setPaymentState($state);

    /**
     * Returns whether the order has payments or not.
     *
     * @return bool
     */
    public function hasPayments();

    /**
     * Returns whether the order has the payment or not.
     *
     * @param PaymentInterface $payment
     *
     * @return bool
     */
    public function hasPayment(PaymentInterface $payment);

    /**
     * Adds the payment.
     *
     * @param PaymentInterface $payment
     *
     * @return $this|PaymentSubjectInterface
     */
    public function addPayment(PaymentInterface $payment);

    /**
     * Removes the payment.
     *
     * @param PaymentInterface $payment
     *
     * @return $this|PaymentSubjectInterface
     */
    public function removePayment(PaymentInterface $payment);

    /**
     * Returns the payments.
     *
     * @return \Doctrine\Common\Collections\Collection|PaymentInterface[]
     */
    public function getPayments();

    /**
     * Returns whether or not the subject is fully paid.
     *
     * @return bool
     */
    public function isPaid();

    /**
     * Returns the payment remaining amount.
     *
     * @return float
     */
    public function getRemainingAmount();
}
