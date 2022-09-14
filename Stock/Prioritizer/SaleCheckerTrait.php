<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;

/**
 * Trait SaleCheckerTrait
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait SaleCheckerTrait
{
    /**
     * Checks whether the sale can be prioritized.
     */
    protected function checkSale(SaleInterface $sale): bool
    {
        if (!$sale instanceof OrderInterface) {
            return false;
        }

        if (!OrderStates::isStockableState($sale->getState())) {
            return false;
        }

        if ($sale->getShipmentState() === ShipmentStates::STATE_COMPLETED) {
            return false;
        }

        return true;
    }
}
