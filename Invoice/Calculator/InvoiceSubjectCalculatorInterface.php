<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Calculator;

use Decimal\Decimal;
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
     */
    public function isInvoiced($itemOrAdjustment): bool;

    /**
     * Calculates the given subject's invoiceable quantity.
     *
     * @param Sale|Item|Adjustment $subject
     */
    public function calculateInvoiceableQuantity($subject, Invoice $ignore = null): Decimal;

    /**
     * Calculates the given subject's creditable quantity.
     *
     * @param Sale|Item|Adjustment $subject
     */
    public function calculateCreditableQuantity($subject, Invoice $ignore = null): Decimal;

    /**
     * Calculates the given subject's invoiced quantity.
     *
     * @param Sale|Item|Adjustment $subject
     */
    public function calculateInvoicedQuantity($subject, Invoice $ignore = null): Decimal;

    /**
     * Calculates the given subject's credited quantity.
     *
     * @param Sale|Item|Adjustment $subject
     * @param Invoice|null         $ignore
     * @param bool                 $adjustment TRUE: only adjustments, FALSE: exclude adjustments and NULL: all credit
     */
    public function calculateCreditedQuantity($subject, Invoice $ignore = null, bool $adjustment = null): Decimal;

    /**
     * Calculates the given subject's sold quantity.
     *
     * @param Sale|Item|Adjustment $subject
     */
    public function calculateSoldQuantity($subject): Decimal;

    /**
     * Builds the invoice quantity map.
     *
     * [
     *     (int) sale item id => [
     *         'sold'     => (Decimal) quantity,
     *         'invoiced' => (Decimal) quantity,
     *         'credited' => (Decimal) quantity,
     *     ]
     * ]
     *
     * @param Subject $subject
     *
     * @return array<int, array<string, Decimal>>
     */
    public function buildInvoiceQuantityMap(Subject $subject): array;

    /**
     * Calculates the total of all subject's invoices.
     */
    public function calculateInvoiceTotal(Subject $subject, string $currency = null): Decimal;

    /**
     * Calculates the total of all subject's credits.
     */
    public function calculateCreditTotal(Subject $subject, string $currency = null): Decimal;
}
