<?php

namespace Ekyna\Component\Commerce\Quote\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model as Common;

/**
 * Interface QuoteInterface
 * @package Ekyna\Component\Commerce\Quote\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface QuoteInterface extends
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
     * @return $this|QuoteInterface
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
     * @return $this|QuoteInterface
     */
    public function setPaymentState($paymentState);

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
     * @return $this|QuoteInterface
     */
    public function setPaidTotal($paidTotal);

    /**
     * Returns whether the quote has payments or not.
     *
     * @return bool
     */
    public function hasPayments();

    /**
     * Returns the payments.
     *
     * @return ArrayCollection|QuotePaymentInterface[]
     */
    public function getPayments();

    /**
     * Returns whether the quote has the payment or not.
     *
     * @param QuotePaymentInterface $payment
     * @return bool
     */
    public function hasPayment(QuotePaymentInterface $payment);

    /**
     * Adds the payment.
     *
     * @param QuotePaymentInterface $payment
     * @return $this|QuoteInterface
     */
    public function addPayment(QuotePaymentInterface $payment);

    /**
     * Removes the payment.
     *
     * @param QuotePaymentInterface $payment
     * @return $this|QuoteInterface
     */
    public function removePayment(QuotePaymentInterface $payment);

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
     * @return $this|QuoteInterface
     */
    public function setCompletedAt(\DateTime $completedAt = null);
}
