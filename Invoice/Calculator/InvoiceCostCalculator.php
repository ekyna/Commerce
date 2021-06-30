<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Calculator;

use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;
use Ekyna\Component\Commerce\Subject\Model\PurchaseCost;

/**
 * Class InvoiceCostCalculator
 * @package Ekyna\Component\Commerce\Invoice\Calculator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceCostCalculator
{
    /**
     * Calculates the invoice purchase costs.
     */
    public function calculate(InvoiceInterface $invoice): PurchaseCost
    {
        $invoiceGood = $invoiceShipping = 0;

        foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_GOOD) as $line) {
            if (!$item = $line->getSaleItem()) {
                continue;
            }

            if ($item->isCompound()) {
                continue;
            }

            if (!$item instanceof StockAssignmentsInterface) {
                continue;
            }

            $assignments = $item->getStockAssignments();

            if ($assignments->isEmpty()) {
                continue;
            }

            $count = $goodSum = $shippingSum = 0;
            foreach ($assignments as $assignment) {
                $unit = $assignment->getStockUnit();
                $goodSum += $unit->getNetPrice();
                $shippingSum += $unit->getShippingPrice();
                $count++;
            }

            $lineGood = $goodSum / $count * $line->getQuantity();
            $lineShipping = $shippingSum / $count * $line->getQuantity();

            $invoiceGood += $lineGood;
            $invoiceShipping += $lineShipping;
        }

        return new PurchaseCost($invoiceGood, $invoiceShipping);
    }
}
