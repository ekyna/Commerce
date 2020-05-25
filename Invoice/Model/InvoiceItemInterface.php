<?php

namespace Ekyna\Component\Commerce\Invoice\Model;

use Ekyna\Component\Commerce\Document\Model\DocumentItemInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\SortableInterface;

/**
 * Interface InvoiceItemInterface
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoiceItemInterface extends DocumentItemInterface, SortableInterface, ResourceInterface
{
    /**
     * Returns the invoice.
     *
     * @return InvoiceInterface|null
     */
    public function getInvoice(): ?InvoiceInterface;

    /**
     * Sets the invoice.
     *
     * @param InvoiceInterface $invoice
     *
     * @return $this|InvoiceItemInterface
     */
    public function setInvoice(InvoiceInterface $invoice = null): InvoiceItemInterface;
}
