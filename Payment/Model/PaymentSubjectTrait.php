<?php

namespace Ekyna\Component\Commerce\Payment\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\CurrencySubjectTrait;
use Ekyna\Component\Commerce\Common\Util\Money;

/**
 * Trait PaymentSubjectTrait
 * @package Ekyna\Component\Commerce\Payment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait PaymentSubjectTrait
{
    use CurrencySubjectTrait;

    /**
     * @var float
     */
    protected $grandTotal;

    /**
     * @var float
     */
    protected $paidTotal;

    /**
     * @var float
     */
    protected $outstandingAccepted;

    /**
     * @var float
     */
    protected $outstandingExpired;

    /**
     * @var float
     */
    protected $outstandingLimit;

    /**
     * @var \DateTime
     */
    protected $outstandingDate;

    /**
     * @var string
     */
    protected $paymentState;

    /**
     * @var \Doctrine\Common\Collections\Collection|PaymentInterface[]
     */
    protected $payments;


    /**
     * Initializes the payment subject.
     */
    protected function initializePaymentSubject()
    {
        $this->grandTotal = 0;
        $this->paidTotal = 0;
        $this->outstandingAccepted = 0;
        $this->outstandingExpired = 0;
        $this->outstandingLimit = 0;

        $this->paymentState = PaymentStates::STATE_NEW;
        $this->payments = new ArrayCollection();
    }

    /**
     * Returns the grand total.
     *
     * @return float
     */
    public function getGrandTotal()
    {
        return $this->grandTotal;
    }

    /**
     * Sets the grand total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setGrandTotal($total)
    {
        $this->grandTotal = $total;

        return $this;
    }

    /**
     * Returns the paid total.
     *
     * @return float
     */
    public function getPaidTotal()
    {
        return $this->paidTotal;
    }

    /**
     * Sets the paid total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setPaidTotal($total)
    {
        $this->paidTotal = $total;

        return $this;
    }

    /**
     * Returns the accepted outstanding total.
     *
     * @return float
     */
    public function getOutstandingAccepted()
    {
        return $this->outstandingAccepted;
    }

    /**
     * Sets the accepted outstanding total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingAccepted($total)
    {
        $this->outstandingAccepted = $total;

        return $this;
    }

    /**
     * Returns the expired outstanding total.
     *
     * @return float
     */
    public function getOutstandingExpired()
    {
        return $this->outstandingExpired;
    }

    /**
     * Sets the expired outstanding total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingExpired($total)
    {
        $this->outstandingExpired = $total;

        return $this;
    }

    /**
     * Returns the outstanding limit.
     *
     * @return float
     */
    public function getOutstandingLimit()
    {
        return $this->outstandingLimit;
    }

    /**
     * Sets the outstanding amount.
     *
     * @param float $amount
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingLimit($amount)
    {
        $this->outstandingLimit = $amount;

        return $this;
    }

    /**
     * Returns the outstanding date.
     *
     * @return \DateTime
     */
    public function getOutstandingDate()
    {
        return $this->outstandingDate;
    }

    /**
     * Sets the outstanding date.
     *
     * @param \DateTime $date
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingDate(\DateTime $date = null)
    {
        $this->outstandingDate = $date;

        return $this;
    }

    /**
     * Returns the payment state.
     *
     * @return string
     */
    public function getPaymentState()
    {
        return $this->paymentState;
    }

    /**
     * Sets the payment state.
     *
     * @param string $state
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setPaymentState($state)
    {
        $this->paymentState = $state;

        return $this;
    }

    /**
     * Returns whether or not the subject has at least one payment.
     *
     * @return bool
     */
    public function hasPayments()
    {
        return 0 < $this->payments->count();
    }

    /**
     * Returns the payments.
     *
     * @return \Doctrine\Common\Collections\Collection|PaymentInterface[]
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * Returns whether or not the subject is fully paid.
     *
     * @return bool
     */
    public function isPaid()
    {
        // TRUE If paid total is greater than or equals grand total
        return 0 <= Money::compare($this->paidTotal, $this->grandTotal, $this->getCurrency()->getCode());
    }

    /**
     * Returns the payment remaining amount.
     *
     * @return float
     */
    public function getRemainingAmount()
    {
        // If fully paid
        if ($this->isPaid()) {
            // Return zero
            return 0;
        }

        // If paid + accepted outstanding equals grand total
        $currency = $this->getCurrency()->getCode();

        if (0 < $this->outstandingAccepted) {
            if (0 === Money::compare($this->paidTotal + $this->outstandingAccepted, $this->grandTotal, $currency)) {
                // Return accepted outstanding amount (for fund release)
                return $this->outstandingAccepted;
            }
        }

        // Return grand total minus paid
        return $this->grandTotal - $this->paidTotal;
    }
}
