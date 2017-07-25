<?php

namespace Ekyna\Component\Commerce\Invoice\Calculator;

use Ekyna\Component\Commerce\Invoice\Model;

/**
 * Class InvoiceCalculatorInterface
 * @package Ekyna\Component\Commerce\Invoice\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoiceCalculatorInterface
{
    /**
     * Calculates the given invoice.
     *
     * @param Model\InvoiceInterface $invoice
     *
     * @return bool
     * @throws \Ekyna\Component\Commerce\Exception\LogicException
     */
    public function calculate(Model\InvoiceInterface $invoice);
}
