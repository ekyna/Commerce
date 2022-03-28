<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Builder;

use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
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
     * Returns the factory helper.
     */
    public function getFactoryHelper(): FactoryHelperInterface;

    /**
     * Returns the invoice calculator.
     */
    public function getInvoiceCalculator(): InvoiceSubjectCalculatorInterface;

    /**
     * Finds or create the invoice line.
     */
    public function findOrCreateGoodLine(
        InvoiceInterface $invoice,
        SaleItemInterface $item
    ): ?InvoiceLineInterface;
}
