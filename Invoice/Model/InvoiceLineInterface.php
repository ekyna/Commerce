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

    /**
     * Returns the children invoice lines.
     *
     * @return \Doctrine\Common\Collections\Collection|InvoiceLineInterface[]
     */
    public function getChildren();

    /**
     * Clears the children.
     *
     * @return $this|InvoiceLineInterface
     */
    public function clearChildren();

    /**
     * Returns the expected.
     *
     * @return float
     */
    public function getExpected();

    /**
     * Sets the expected.
     *
     * @param float $expected
     *
     * @return $this|InvoiceLineInterface
     */
    public function setExpected($expected);

    /**
     * Returns the available.
     *
     * @return float
     */
    public function getAvailable();

    /**
     * Sets the available.
     *
     * @param float $available
     *
     * @return $this|InvoiceLineInterface
     */
    public function setAvailable($available);
}
