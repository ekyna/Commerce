<?php declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Model\Margin;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface as Sale;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface as Item;

/**
 * Interface MarginCalculatorInterface
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface MarginCalculatorInterface
{
    /**
     * Calculates the sale margin.
     *
     * @param SaleInterface $sale The sale
     *
     * @return Margin|null
     */
    public function calculateSale(SaleInterface $sale): ?Margin;

    /**
     * Calculates the sale item margin.
     *
     * @param Item $item
     *
     * @return Margin|null
     */
    public function calculateSaleItem(Item $item): ?Margin;

    /**
     * Calculates the sale shipment margin.
     *
     * @param Sale $sale
     *
     * @return Margin|null
     */
    public function calculateSaleShipment(Sale $sale): ?Margin;
}
