<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Model\Margin;
use Ekyna\Component\Commerce\Common\Model\MarginCacheTrait;
use Ekyna\Component\Commerce\Common\Model\SaleInterface as Sale;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface as Item;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentCostCalculatorInterface;
use Ekyna\Component\Commerce\Stat\Calculator\StatFilter;

use function spl_object_id;

/**
 * Class MarginCalculator
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MarginCalculator implements MarginCalculatorInterface
{
    use MarginCacheTrait;

    private readonly AmountCalculatorFactory         $calculatorFactory;
    private readonly ItemCostCalculatorInterface     $itemCostCalculator;
    private readonly ShipmentCostCalculatorInterface $shipmentCostCalculator;

    private ?AmountCalculatorInterface $amountCalculator = null;

    /**
     * @internal Use Calculator factory
     */
    public function __construct(
        private readonly string      $currency,
        private readonly ?StatFilter $filter = null
    ) {
    }

    public function setCalculatorFactory(AmountCalculatorFactory $factory): void
    {
        $this->calculatorFactory = $factory;
    }

    public function setItemCostCalculator(ItemCostCalculatorInterface $calculator): void
    {
        $this->itemCostCalculator = $calculator;
    }

    public function setShipmentCostCalculator(ShipmentCostCalculatorInterface $calculator): void
    {
        $this->shipmentCostCalculator = $calculator;
    }

    public function calculateSale(Sale $sale): Margin
    {
        $key = 'sale_' . spl_object_id($sale);
        if ($margin = $this->get($key)) {
            return $margin;
        }

        $margin = new Margin();
        $this->set($key, $margin);

        if (!$sale->hasItems() || $sale->isSample()) {
            return $margin;
        }

        $this->getAmountCalculator()->calculateSale($sale);

        if (!$this->mergeItemsMargin($sale->getItems(), $margin)) {
            return $margin;
        }

        foreach ($sale->getAdjustments(AdjustmentTypes::TYPE_DISCOUNT) as $adjustment) {
            $discountAmount = $this->getAmountCalculator()->calculateSaleDiscount($adjustment);
            $margin->addRevenueProduct($discountAmount->getBase()->negate());
        }

        $margin->merge($this->calculateSaleShipment($sale));

        return $margin;
    }

    public function calculateSaleItem(Item $item, bool $single = false): Margin
    {
        $key = 'sale_item_' . spl_object_id($item) . ($single ? '_single' : '');
        if ($margin = $this->get($key)) {
            return $margin;
        }

        $margin = new Margin();
        $this->set($key, $margin);

        // Add item cost
        $cost = $this->itemCostCalculator->calculateSaleItem($item, null, $single);
        $margin->addCost($cost);

        // Add item revenue
        $amount = $this->getAmountCalculator()->calculateSaleItem($item, null, $single, !$single);
        $margin->addRevenueProduct($amount->getBase());

        return $margin;
    }

    public function calculateSaleShipment(Sale $sale): Margin
    {
        $key = 'sale_shipment_' . spl_object_id($sale);
        if ($margin = $this->get($key)) {
            return $margin;
        }

        $this->set($key, $margin = new Margin());

        // Sample sale case
        if ($sale->isSample()) {
            return $margin;
        }

        $shipmentAmount = $this->getAmountCalculator()->calculateSaleShipment($sale);
        $margin->addRevenueShipment($shipmentAmount->getBase());

        $cost = $this
            ->shipmentCostCalculator
            ->calculateSale($sale, $this->currency);

        $margin->addCost($cost);

        return $margin;
    }

    /**
     * @param iterable<SaleItemInterface> $items
     */
    private function mergeItemsMargin(iterable $items, Margin $margin): bool
    {
        $found = false;
        foreach ($items as $item) {
            if ($item->isPrivate() || $this->isItemSkipped($item)) {
                continue;
            }

            $margin->merge($this->calculateSaleItem($item));
            $found = true;

            if (!$item->hasChildren()) {
                continue;
            }

            $this->mergeItemsMargin($item->getChildren(), $margin);
        }

        return $found;
    }

    private function getAmountCalculator(): AmountCalculatorInterface
    {
        if ($this->amountCalculator) {
            return $this->amountCalculator;
        }

        return $this->amountCalculator = $this->calculatorFactory->create($this->currency);
    }

    /**
     * Returns whether the given item should be skipped regarding the configured filter.
     */
    private function isItemSkipped(Item $item): bool
    {
        if (!$this->filter) {
            return false;
        }

        if (!$item->hasSubjectIdentity()) {
            return false;
        }

        return $this->filter->hasSubject($item->getSubjectIdentity()) xor !$this->filter->isExcludeSubjects();
    }
}
