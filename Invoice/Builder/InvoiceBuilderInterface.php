<?php

namespace Ekyna\Component\Commerce\Invoice\Builder;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Document\Builder\DocumentBuilderInterface;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCalculatorInterface;

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
     * @return InvoiceCalculatorInterface
     */
    public function getInvoiceCalculator();
}
