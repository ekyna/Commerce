<?php

namespace Ekyna\Component\Commerce\Payment\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\CurrencySubjectTrait;

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
    protected $outstandingTotal;

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
        $this->outstandingTotal = 0;
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
     * Returns the outstanding total.
     *
     * @return float
     */
    public function getOutstandingTotal()
    {
        return $this->outstandingTotal;
    }

    /**
     * Sets the outstanding total.
     *
     * @param float $total
     *
     * @return $this|PaymentSubjectInterface
     */
    public function setOutstandingTotal($total)
    {
        $this->outstandingTotal = $total;

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
     * @inheritdoc
     */
    public function getRemainingAmount()
    {
        $paid = $this->paidTotal - $this->outstandingTotal;

        if ($paid >= $this->grandTotal) {
            return 0;
        }

        return $this->grandTotal - $paid;
    }

}
