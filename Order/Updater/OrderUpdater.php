<?php

namespace Ekyna\Component\Commerce\Order\Updater;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Calculator\MarginCalculatorFactory;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;

class OrderUpdater implements OrderUpdaterInterface
{
    /**
     * @var MarginCalculatorFactory
     */
    private $marginCalculatorFactory;


    /**
     * Constructor.
     *
     * @param MarginCalculatorFactory $marginCalculatorFactory
     */
    public function __construct(MarginCalculatorFactory $marginCalculatorFactory)
    {
        $this->marginCalculatorFactory = $marginCalculatorFactory;
    }

    /**
     * @inheritDoc
     */
    public function updateMarginTotals(OrderInterface $order): bool
    {
        $changed = false;

        $result = $this->marginCalculatorFactory->create(null, true)->calculateSale($order);

        // Margin
        $total = round($result ? $result->getAmount() : 0, 5);
        if ($total !== $order->getMarginTotal()) {
            $order->setMarginTotal($total);
            $changed = true;
        }

        // Revenue
        $total = round($result ? $result->getSellingPrice() : 0, 5);
        if ($total !== $order->getRevenueTotal()) {
            $order->setRevenueTotal($total);
            $changed = true;
        }

        return $changed;
    }

    /**
     * @inheritDoc
     */
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
     * @param Collection|OrderItemInterface[] $items
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

    /**
     * Calculate
     *
     * @param OrderItemInterface $item
     *
     * @return int
     */
    private function calculateItemCount(OrderItemInterface $item): int
    {
        $count = 0;

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

        return $count;
    }
}
