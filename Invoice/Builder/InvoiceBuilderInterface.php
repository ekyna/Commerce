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
    public function getSaleFactory(): SaleFactoryInterface;

    /**
     * Returns the invoice calculator.
     *
     * @return InvoiceSubjectCalculatorInterface
     */
    public function getInvoiceCalculator(): InvoiceSubjectCalculatorInterface;

    /**
     * Finds or create the invoice line.
     *
     * @param InvoiceInterface $invoice
     * @param SaleItemInterface $item
     * @param float $available
     * @param float|null $expected
     *
     * @return InvoiceLineInterface|null
     */
    public function findOrCreateGoodLine(
        InvoiceInterface $invoice,
        SaleItemInterface $item,
        float $available,
        float $expected = null
    ): ?InvoiceLineInterface;
}
