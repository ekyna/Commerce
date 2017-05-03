<?php

namespace Ekyna\Component\Commerce\Credit\Util;

use Ekyna\Component\Commerce\Credit\Model\CreditItemInterface;
use Ekyna\Component\Commerce\Shipment\Util\ShipmentUtil;

/**
 * Class CreditUtil
 * @package Ekyna\Component\Commerce\Credit
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class CreditUtil
{
    /**
     * Calculate the credit item creditable quantity.
     *
     * @param CreditItemInterface $item
     *
     * @return float
     */
    static public function calculateCreditableQuantity(CreditItemInterface $item)
    {
        $saleItem = $item->getSaleItem();
        $sale = $item->getCredit()->getSale();

        // Base creditable quantity : sale item total quantity - sale item shipped quantity.
        $quantity = $saleItem->getTotalQuantity() - ShipmentUtil::calculateShippedQuantity($saleItem);

        // Debit credit's sale item quantities
        foreach ($sale->getCredits() as $credit) {
            // Ignore the current item's credit
            if ($credit === $item->getCredit()) {
                continue;
            }

            foreach ($credit->getItems() as $creditItem) {
                if ($creditItem->getSaleItem() === $saleItem) {
                    $quantity -= $creditItem->getQuantity();
                }
            }
        }

        return $quantity;
    }
}
