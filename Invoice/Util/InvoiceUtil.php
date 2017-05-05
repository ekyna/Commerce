<?php

namespace Ekyna\Component\Commerce\Invoice\Util;

use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Invoice\Model;

/**
 * Class InvoiceUtil
 * @package Ekyna\Component\Commerce\Invoice
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class InvoiceUtil
{
    /**
     * Calculate the item's max credit quantity.
     *
     * @param Model\InvoiceLineInterface $line
     *
     * @return float
     *
     * @throws LogicException
     */
    static public function calculateMaxCreditQuantity(Model\InvoiceLineInterface $line)
    {
        if ($line->getInvoice()->getType() !== Model\InvoiceTypes::TYPE_CREDIT) {
            throw new LogicException(sprintf(
                "Expected invoice with type '%s'.",
                Model\InvoiceTypes::TYPE_CREDIT
            ));
        }
        if ($line->getType() !== Model\InvoiceLineTypes::TYPE_GOOD) {
            throw new LogicException(sprintf(
                "Expected invoice line with type '%s'.",
                Model\InvoiceLineTypes::TYPE_GOOD
            ));
        }

        $saleItem = $line->getSaleItem();
        $sale = $line->getInvoice()->getSale();

        $quantity = 0;

        foreach ($sale->getInvoices() as $invoice) {
            // Ignore the current item's invoice
            if ($invoice === $line->getInvoice()) {
                continue;
            }

            foreach ($invoice->getLines() as $invoiceLine) {
                if ($invoiceLine->getSaleItem() === $saleItem) {
                    if (Model\InvoiceTypes::TYPE_INVOICE === $invoice->getType()) {
                        $quantity += $invoiceLine->getQuantity();
                    } elseif (Model\InvoiceTypes::TYPE_CREDIT === $invoice->getType()) {
                        $quantity -= $invoiceLine->getQuantity();
                    }
                }
            }
        }

        return $quantity;
    }

    /**
     * Calculate the item's max invoice quantity.
     *
     * @param Model\InvoiceLineInterface $line
     *
     * @return float
     *
     * @throws LogicException
     */
    static public function calculateMaxInvoiceQuantity(Model\InvoiceLineInterface $line)
    {
        if ($line->getInvoice()->getType() !== Model\InvoiceTypes::TYPE_INVOICE) {
            throw new LogicException(sprintf(
                "Expected invoice with type '%s'.",
                Model\InvoiceTypes::TYPE_INVOICE
            ));
        }
        if ($line->getType() !== Model\InvoiceLineTypes::TYPE_GOOD) {
            throw new LogicException(sprintf(
                "Expected invoice line with type '%s'.",
                Model\InvoiceLineTypes::TYPE_GOOD
            ));
        }

        $saleItem = $line->getSaleItem();
        $sale = $line->getInvoice()->getSale();

        // Base quantity is the sale item total quantity.
        $quantity = $saleItem->getTotalQuantity();

        // Debit invoice's sale item quantities
        foreach ($sale->getInvoices() as $invoice) {
            // Ignore the current item's invoice
            if ($invoice === $line->getInvoice()) {
                continue;
            }

            foreach ($invoice->getLines() as $invoiceLine) {
                if ($invoiceLine->getSaleItem() === $saleItem) {
                    if (Model\InvoiceTypes::TYPE_INVOICE === $invoice->getType()) {
                        $quantity -= $invoiceLine->getQuantity();
                    } elseif (Model\InvoiceTypes::TYPE_CREDIT === $invoice->getType()) {
                        $quantity += $invoiceLine->getQuantity();
                    }
                }
            }
        }

        return $quantity;
    }
}
