<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Updater;

use Decimal\Decimal;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Calculator\MarginCalculatorFactory;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;

/**
 * Class OrderUpdater
 * @package Ekyna\Component\Commerce\Order\Updater
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderUpdater implements OrderUpdaterInterface
{
    private MarginCalculatorFactory $marginCalculatorFactory;

    public function __construct(MarginCalculatorFactory $marginCalculatorFactory)
    {
        $this->marginCalculatorFactory = $marginCalculatorFactory;
    }

    public function updateMarginTotals(OrderInterface $order): bool
    {
        $changed = false;

        $result = $this->marginCalculatorFactory->create(null, true)->calculateSale($order);

        // Margin
        $total = $result ? $result->getAmount() : new Decimal(0);
        if (!$total->equals($order->getMarginTotal())) {
            $order->setMarginTotal($total);
            $changed = true;
        }

        // Revenue
        $total = $result ? $result->getSellingPrice() : new Decimal(0);
        if (!$total->equals($order->getRevenueTotal())) {
            $order->setRevenueTotal($total);
            $changed = true;
        }

        return $changed;
    }

    public function updateItemsCount(OrderInterface $order): bool
    {
        $count = $this->calculateItemsCount($order->getItems());

        if ($count !== $order->getItemsCount()) {
            $order->setItemsCount($count);

            return true;
        }

        return false;
    }

    /**
     * Calculates the items count.
     *
     * @param Collection<int, OrderItemInterface> $items
     *
     * @return int
     */
    private function calculateItemsCount(Collection $items): int
    {
        $count = 0;

        foreach ($items as $item) {
            $count += $this->calculateItemCount($item);
        }

        return $count;
    }

    private function calculateItemCount(OrderItemInterface $item): int
    {
        $count = new Decimal(0);

        if (!$item->isCompound()) {
            if ($item->hasStockAssignments()) {
                foreach ($item->getStockAssignments() as $assignment) {
                    $count += $assignment->getSoldQuantity();
                }
            } else {
                $count += $item->getTotalQuantity();
            }
        }

        $count += $this->calculateItemsCount($item->getChildren());

        return $count->toInt();
    }
}
