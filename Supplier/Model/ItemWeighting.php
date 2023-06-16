<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Model;

use Decimal\Decimal;

/**
 * Class ItemWeighting
 * @package Ekyna\Component\Commerce\Supplier\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
final class ItemWeighting
{
    private readonly Weighting $order;

    public function __construct(
        public readonly Decimal $weight,
        public readonly Decimal $price,
        public readonly Decimal $quantity,
    ) {
    }

    public function setOrderWeighting(Weighting $order): void
    {
        $this->order = $order;
    }

    public function resolve(): Decimal
    {
        if (!$this->order->missingWeight) {
            return $this->weight;
        }

        if (!$this->order->missingPrice) {
            return $this->price;
        }

        return $this->quantity;
    }
}
