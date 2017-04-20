<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Calculator;

use Decimal\Decimal;
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
        $invoiceGood = $invoiceShipping = new Decimal(0);

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

            $count = 0;
            $goodSum = new Decimal(0);
            $shippingSum = new Decimal(0);
            foreach ($assignments as $assignment) {
                if (null === $unit = $assignment->getStockUnit()) {
                    continue;
                }

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
