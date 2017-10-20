<?php

namespace Ekyna\Component\Commerce\Invoice\Model;

use Ekyna\Component\Commerce\Document\Model\DocumentLineInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface InvoiceLineInterface
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoiceLineInterface extends DocumentLineInterface, ResourceInterface
{
    /**
     * Returns the invoice.
     *
     * @return InvoiceInterface
     */
    public function getInvoice();

    /**
     * Sets the invoice.
     *
     * @param InvoiceInterface $invoice
     *
     * @return $this|InvoiceLineInterface
     */
    public function setInvoice(InvoiceInterface $invoice = null);
}
