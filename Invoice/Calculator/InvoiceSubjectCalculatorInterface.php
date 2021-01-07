<?php

namespace Ekyna\Component\Commerce\Invoice\Calculator;

use Ekyna\Component\Commerce\Common\Model\SaleAdjustmentInterface as Adjustment;
use Ekyna\Component\Commerce\Common\Model\SaleInterface as Sale;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface as Item;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface as Invoice;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface as Subject;

/**
 * Interface InvoiceSubjectCalculatorInterface
 * @package Ekyna\Component\Commerce\Invoice\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoiceSubjectCalculatorInterface
{
    /**
     * Returns whether the sale item or adjustment is invoiced.
     *
     * @param Item|Adjustment $itemOrAdjustment
     *
     * @return bool
     */
    public function isInvoiced($itemOrAdjustment): bool;

    /**
     * Calculates the given subject's invoiceable quantity.
     *
     * @param Sale|Item|Adjustment $subject
     * @param Invoice|null         $ignore
     *
     * @return float
     */
    public function calculateInvoiceableQuantity($subject, Invoice $ignore = null): float;

    /**
     * Calculates the given subject's creditable quantity.
     *
     * @param Sale|Item|Adjustment $subject
     * @param Invoice|null         $ignore
     *
     * @return float
     */
    public function calculateCreditableQuantity($subject, Invoice $ignore = null): float;

    /**
     * Calculates the given subject's invoiced quantity.
     *
     * @param Sale|Item|Adjustment $subject
     * @param Invoice|null         $ignore
     *
     * @return float
     */
    public function calculateInvoicedQuantity($subject, Invoice $ignore = null): float;

    /**
     * Calculates the given subject's credited quantity.
     *
     * @param Sale|Item|Adjustment $subject
     * @param Invoice|null         $ignore
     * @param bool                 $adjustment TRUE: only adjustments, FALSE: exclude adjustments and NULL: all credits
     *
     * @return float
     */
    public function calculateCreditedQuantity($subject, Invoice $ignore = null, bool $adjustment = null): float;

    /**
     * Calculates the given subject's sold quantity.
     *
     * @param Sale|Item|Adjustment $subject
     *
     * @return float
     */
    public function calculateSoldQuantity($subject): float;

    /**
     * Builds the invoice quantity map.
     *
     * [
     *     (int) sale item id => [
     *         'sold'     => (float) quantity,
     *         'invoiced' => (float) quantity,
     *         'credited' => (float) quantity,
     *     ]
     * ]
     *
     * @param Subject $subject
     *
     * @return array
     */
    public function buildInvoiceQuantityMap(Subject $subject): array;

    /**
     * Calculates the total of all subject's invoices.
     *
     * @param Subject     $subject
     * @param string|null $currency
     *
     * @return float
     */
    public function calculateInvoiceTotal(Subject $subject, string $currency = null): float;

    /**
     * Calculates the total of all subject's credits.
     *
     * @param Subject     $subject
     * @param string|null $currency
     *
     * @return float
     */
    public function calculateCreditTotal(Subject $subject, string $currency = null): float;
}
