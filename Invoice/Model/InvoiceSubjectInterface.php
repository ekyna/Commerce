<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Model;

use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\Common\Collections\Collection;

/**
 * Interface InvoiceSubjectInterface
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoiceSubjectInterface
{
    public function getInvoiceTotal(): Decimal;

    public function setInvoiceTotal(Decimal $total): InvoiceSubjectInterface;

    public function getCreditTotal(): Decimal;

    public function setCreditTotal(Decimal $total): InvoiceSubjectInterface;

    public function getInvoiceState(): string;

    public function setInvoiceState(string $state): InvoiceSubjectInterface;

    public function isAutoInvoice(): bool;

    /**
     * @return $this|InvoiceSubjectInterface
     */
    public function setAutoInvoice(bool $autoInvoice): InvoiceSubjectInterface;

    public function hasInvoices(): bool;

    /**
     * @param bool $filter TRUE for invoices, FALSE for credits, NULL for all
     *
     * @return Collection|InvoiceInterface[]
     */
    public function getInvoices(bool $filter = null): Collection;

    public function hasInvoice(InvoiceInterface $invoice): bool;

    public function addInvoice(InvoiceInterface $invoice): InvoiceSubjectInterface;

    public function removeInvoice(InvoiceInterface $invoice): InvoiceSubjectInterface;

    /**
     * @param bool $latest Whether to return the last invoice date instead of the first.
     */
    public function getInvoicedAt(bool $latest = false): ?DateTimeInterface;

    /**
     * Returns whether the sale has been fully invoiced (ignoring credits).
     */
    public function isFullyInvoiced(): bool;
}
