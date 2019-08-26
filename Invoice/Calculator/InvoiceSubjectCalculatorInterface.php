<?php

namespace Ekyna\Component\Commerce\Invoice\Calculator;

use Ekyna\Component\Commerce\Common\Model\SaleAdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;

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
     * @param SaleItemInterface|SaleAdjustmentInterface $itemOrAdjustment
     *
     * @return bool
     */
    public function isInvoiced($itemOrAdjustment): bool;

    /**
     * Calculates the given subject's invoiceable quantity.
     *
     * @param SaleInterface|SaleItemInterface|SaleAdjustmentInterface $subject
     * @param Invoice\InvoiceInterface                                $ignore
     *
     * @return float
     */
    public function calculateInvoiceableQuantity($subject, Invoice\InvoiceInterface $ignore = null): float;

    /**
     * Calculates the given subject's creditable quantity.
     *
     * @param SaleInterface|SaleItemInterface|SaleAdjustmentInterface $subject
     * @param Invoice\InvoiceInterface                                $ignore
     *
     * @return float
     */
    public function calculateCreditableQuantity($subject, Invoice\InvoiceInterface $ignore = null): float;

    /**
     * Calculates the given subject's invoiced quantity.
     *
     * @param SaleInterface|SaleItemInterface|SaleAdjustmentInterface $subject
     * @param Invoice\InvoiceInterface                                $ignore
     *
     * @return float
     */
    public function calculateInvoicedQuantity($subject, Invoice\InvoiceInterface $ignore = null): float;

    /**
     * Calculates the given subject's credited quantity.
     *
     * @param SaleInterface|SaleItemInterface|SaleAdjustmentInterface $subject
     * @param Invoice\InvoiceInterface                                $ignore
     *
     * @return float
     */
    public function calculateCreditedQuantity($subject, Invoice\InvoiceInterface $ignore = null): float;

    /**
     * Calculates the total of all subject's invoices.
     *
     * @param Invoice\InvoiceSubjectInterface $subject
     * @param string                          $currency
     *
     * @return float
     */
    public function calculateInvoiceTotal(Invoice\InvoiceSubjectInterface $subject, string $currency = null): float;

    /**
     * Calculates the total of all subject's credits.
     *
     * @param Invoice\InvoiceSubjectInterface $subject
     * @param string                          $currency
     *
     * @return float
     */
    public function calculateCreditTotal(Invoice\InvoiceSubjectInterface $subject, string $currency = null): float;

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
     * @param Invoice\InvoiceSubjectInterface $subject
     *
     * @return array
     */
    public function buildInvoiceQuantityMap(Invoice\InvoiceSubjectInterface $subject): array;
}