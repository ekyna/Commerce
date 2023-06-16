<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;

/**
 * Interface SupplierOrderItemCalculatorInterface
 * @package Ekyna\Component\Commerce\Supplier\Calculator
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface SupplierOrderItemCalculatorInterface
{
    /**
     * Calculates the resulting supplier order item product price.
     *
     * @throws LogicException
     */
    public function calculateItemProductPrice(SupplierOrderItemInterface $item): Decimal;

    /**
     * Calculates the resulting supplier order item shipping price.
     *
     * @throws LogicException
     */
    public function calculateItemShippingPrice(SupplierOrderItemInterface $item): Decimal;
}
