<?php

namespace Ekyna\Component\Commerce\Invoice\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Trait InvoiceSubjectTrait
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait InvoiceSubjectTrait
{
    /**
     * @var float
     */
    protected $invoiceTotal;

    /**
     * @var string
     */
    protected $invoiceState;

    /**
     * @var \Doctrine\Common\Collections\Collection|InvoiceInterface[]
     */
    protected $invoices;


    /**
     * Initializes the invoices.
     */
    protected function initializeInvoiceSubject()
    {
        $this->invoiceTotal = 0;
        $this->invoiceState = InvoiceStates::STATE_NEW;
        $this->invoices = new ArrayCollection();
    }

    /**
     * Returns the invoice total.
     *
     * @return float
     */
    public function getInvoiceTotal()
    {
        return $this->invoiceTotal;
    }

    /**
     * Sets the invoice total.
     *
     * @param float $total
     *
     * @return InvoiceSubjectTrait
     */
    public function setInvoiceTotal($total)
    {
        $this->invoiceTotal = (float)$total;

        return $this;
    }

    /**
     * Returns the invoice state.
     *
     * @return string
     */
    public function getInvoiceState()
    {
        return $this->invoiceState;
    }

    /**
     * Sets the invoice state.
     *
     * @param string $invoiceState
     *
     * @return $this|InvoiceSubjectInterface
     */
    public function setInvoiceState($invoiceState)
    {
        $this->invoiceState = $invoiceState;

        return $this;
    }

    /**
     * Returns whether the order has invoices or not.
     *
     * @return bool
     */
    public function hasInvoices()
    {
        return 0 < $this->invoices->count();
    }

    /**
     * Returns the invoices.
     *
     * @return \Doctrine\Common\Collections\Collection|InvoiceInterface[]
     */
    public function getInvoices()
    {
        return $this->invoices;
    }

    /**
     * Returns the first invoice date.
     *
     * @return \DateTime|null
     */
    public function getInvoicedAt()
    {
        if (0 == $this->invoices->count()) {
            return null;
        }

        $criteria = Criteria::create();
        $criteria
            ->andWhere(Criteria::expr()->eq('type', InvoiceTypes::TYPE_INVOICE))
            ->orderBy(['createdAt' => Criteria::ASC]);

        /** @var ArrayCollection $invoices */
        $invoices = $this->invoices;
        $invoices = $invoices->matching($criteria);

        /** @var InvoiceInterface $invoice */
        if (false !== $invoice = $invoices->first()) {
            return $invoice->getCreatedAt();
        }

        return null;
    }
}
