<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Supplier\Model\ItemWeighting;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Commerce\Supplier\Model\Weighting;

/**
 * Class WeightingCalculator
 * @package Ekyna\Component\Commerce\Supplier\Calculator
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class WeightingCalculator implements WeightingCalculatorInterface
{
    /**
     * @var array<string, Weighting>
     */
    private array $cache = [];

    /**
     * Clears the weighting cache.
     */
    public function onClear(): void
    {
        $this->cache = [];
    }

    /**
     * @inheritDoc
     */
    public function getWeighting(SupplierOrderItemInterface $item): ItemWeighting
    {
        return $this->get($item)->getItem($item);
    }

    private function get(SupplierOrderItemInterface $item): Weighting
    {
        if (null === $order = $item->getOrder()) {
            throw new LogicException('Supplier order item\'s order must be set at this point.');
        }

        $orderKey = spl_object_id($order);
        if (!isset($this->cache[$orderKey])) {
            $this->calculate($order);
        }

        return $this->cache[$orderKey];
    }

    /**
     * Calculates order item's weighting.
     */
    private function calculate(SupplierOrderInterface $order): void
    {
        $orderKey = spl_object_id($order);

        if (isset($this->cache[$orderKey])) {
            return;
        }

        $amount = [];
        $total = [
            'weight'   => new Decimal(0),
            'price'    => new Decimal(0),
            'quantity' => new Decimal(0),
        ];

        // Gather amounts and totals
        $missingWeight = $missingPrice = true;
        foreach ($order->getItems() as $item) {
            $amount[spl_object_id($item)] = [
                'item'     => $item,
                'weight'   => $weight = $item->getWeight(),
                'price'    => $price = $item->getNetPrice(),
                'quantity' => new Decimal(1),
            ];

            $quantity = $item->getQuantity();

            $total['weight'] += $weight * $quantity;
            $total['price'] += $price * $quantity;
            $total['quantity'] += $quantity;

            if (0 < $weight) { // TODO Use packaging format
                $missingWeight = false;
            }

            if (0 < $price) {
                $missingPrice = false;
            }
        }

        // Calculate weighting
        /**
         * @param array<string, Decimal> $data
         * @param string $key
         * @return Decimal
         */
        $calculate = static function (array $data, string $key) use ($total): Decimal {
            if ($total[$key]->isZero()) {
                return new Decimal(0);
            }

            return $data[$key]->div($total[$key])->round(15);
        };

        $weighting = new Weighting(
            $missingWeight,
            $missingPrice,
        );

        foreach ($amount as $data) {
            $weighting->addItem($data['item'],
                new ItemWeighting(
                    $calculate($data, 'weight'),
                    $calculate($data, 'price'),
                    $calculate($data, 'quantity'),
                ));
        }

        $this->cache[$orderKey] = $weighting;
    }
}
