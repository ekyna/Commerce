<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Calculator;

use Ekyna\Component\Commerce\Common\Model\Margin;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceItemInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;

/**
 * Interface InvoiceMarginCalculatorInterface
 * @package Ekyna\Component\Commerce\Invoice\Calculator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface InvoiceMarginCalculatorInterface
{
    /**
     * Calculates the invoice margin.
     */
    public function calculateInvoice(InvoiceInterface $invoice): Margin;

    /**
     * Calculates the invoice line margin.
     */
    public function calculateInvoiceLine(InvoiceLineInterface $line, bool $single = false): Margin;

    /**
     * Calculates the invoice item margin.
     */
    public function calculateInvoiceItem(InvoiceItemInterface $item): Margin;
}
