<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Model;

use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

/**
 * Trait InvoiceSubjectTrait
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait InvoiceSubjectTrait
{
    protected Decimal    $invoiceTotal;
    protected Decimal    $creditTotal;
    protected string     $invoiceState;
    protected bool       $autoInvoice;
    protected Collection $invoices;


    protected function initializeInvoiceSubject(): void
    {
        $this->invoiceTotal = new Decimal(0);
        $this->creditTotal = new Decimal(0);
        $this->invoiceState = InvoiceStates::STATE_NEW;
        $this->autoInvoice = true;
        $this->invoices = new ArrayCollection();
    }

    public function getInvoiceTotal(): Decimal
    {
        return $this->invoiceTotal;
    }

    /**
     * @return $this|InvoiceSubjectInterface
     */
    public function setInvoiceTotal(Decimal $total): InvoiceSubjectInterface
    {
        $this->invoiceTotal = $total;

        return $this;
    }

    public function getCreditTotal(): Decimal
    {
        return $this->creditTotal;
    }

    /**
     * @return $this|InvoiceSubjectInterface
     */
    public function setCreditTotal(Decimal $total): InvoiceSubjectInterface
    {
        $this->creditTotal = $total;

        return $this;
    }

    public function getInvoiceState(): string
    {
        return $this->invoiceState;
    }

    /**
     * @return $this|InvoiceSubjectInterface
     */
    public function setInvoiceState(string $state): InvoiceSubjectInterface
    {
        $this->invoiceState = $state;

        return $this;
    }

    public function isAutoInvoice(): bool
    {
        return $this->autoInvoice;
    }

    /**
     * @return $this|InvoiceSubjectInterface
     */
    public function setAutoInvoice(bool $autoInvoice): InvoiceSubjectInterface
    {
        $this->autoInvoice = $autoInvoice;

        return $this;
    }

    public function hasInvoices(): bool
    {
        return 0 < $this->invoices->count();
    }

    /**
     * @param bool $filter TRUE for invoices, FALSE for credits, NULL for all
     *
     * @return Collection|InvoiceInterface[]
     */
    public function getInvoices(bool $filter = null): Collection
    {
        if (null === $filter) {
            return $this->invoices;
        }

        return $this->invoices->filter(function (InvoiceInterface $invoice) use ($filter) {
            return $filter xor $invoice->isCredit();
        });
    }

    /**
     * @param bool $latest Whether to return the last invoice date instead of the first.
     */
    public function getInvoicedAt(bool $latest = false): ?DateTimeInterface
    {
        if (0 === $this->invoices->count()) {
            return null;
        }

        $criteria = Criteria::create();
        $criteria
            ->andWhere(Criteria::expr()->eq('credit', false))
            ->orderBy(['createdAt' => $latest ? Criteria::DESC : Criteria::ASC]);

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
