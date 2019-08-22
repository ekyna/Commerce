<?php

namespace Ekyna\Component\Commerce\Invoice\Builder;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Document\Builder\DocumentBuilderInterface;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;

/**
 * Interface InvoiceBuilderInterface
 * @package Ekyna\Component\Commerce\Invoice\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoiceBuilderInterface extends DocumentBuilderInterface
{
    /**
     * Returns the sale factory.
     *
     * @return SaleFactoryInterface
     */
    public function getSaleFactory();

    /**
     * Returns the invoice calculator.
     *
     * @return InvoiceSubjectCalculatorInterface
     */
    public function getInvoiceCalculator();

    /**
     * Finds or create the invoice line.
     *
     * @param InvoiceInterface  $invoice
     * @param SaleItemInterface $item
     * @param float             $available
     * @param float             $expected
     *
     * @return InvoiceLineInterface
     */
    public function findOrCreateGoodLine(InvoiceInterface $invoice, SaleItemInterface $item, $available, $expected = null);
}
