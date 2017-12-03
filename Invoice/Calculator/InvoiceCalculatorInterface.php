<?php

namespace Ekyna\Component\Commerce\Invoice\Calculator;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;

/**
 * Interface InvoiceCalculatorInterface
 * @package Ekyna\Component\Commerce\Invoice\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoiceCalculatorInterface
{
    /**
     * Calculate the line's max quantity.
     *
     * @param Invoice\InvoiceLineInterface $line
     *
     * @return float
     */
    public function calculateMaxQuantity(Invoice\InvoiceLineInterface $line);

    /**
     * Calculates the invoice line invoiceable quantity.
     *
     * @param Invoice\InvoiceLineInterface $line
     *
     * @return float
     */
    public function calculateInvoiceableQuantity(Invoice\InvoiceLineInterface $line);

    /**
     * Calculates the invoice line creditable quantity.
     *
     * @param Invoice\InvoiceLineInterface $line
     *
     * @return float
     */
    public function calculateCreditableQuantity(Invoice\InvoiceLineInterface $line);

    /**
     * Calculates the invoiced (minus credited) quantity for the given sale item.
     *
     * @param Common\SaleItemInterface $item
     *
     * @return float
     */
    public function calculateInvoicedQuantity(Common\SaleItemInterface $item);

    /**
     * Calculates the credited quantity for the given sale item.
     *
     * @param Common\SaleItemInterface $item
     *
     * @return float
     */
    public function calculateCreditedQuantity(Common\SaleItemInterface $item);

    /**
     * Calculates the canceled quantity for the given sale item.
     *
     * @param Common\SaleItemInterface $item
     *
     * @return float
     */
    public function calculateCanceledQuantity(Common\SaleItemInterface $item);

    /**
     * Calculates the total of all subject's invoices.
     *
     * @param Invoice\InvoiceSubjectInterface $subject
     *
     * @return float
     */
    public function calculateInvoiceTotal(Invoice\InvoiceSubjectInterface $subject);

    /**
     * Calculates the total of all subject's credits.
     *
     * @param Invoice\InvoiceSubjectInterface $subject
     *
     * @return float
     */
    public function calculateCreditTotal(Invoice\InvoiceSubjectInterface $subject);

    /**
     * Calculates the total of all subject's credits which are not linked to return shipment.
     *
     * @param Invoice\InvoiceSubjectInterface $subject
     *
     * @return float
     */
    public function calculateCanceledTotal(Invoice\InvoiceSubjectInterface $subject);

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
    public function buildInvoiceQuantityMap(Invoice\InvoiceSubjectInterface $subject);
}
