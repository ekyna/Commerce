<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Linker;

use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryItemInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;

/**
 * Interface SupplierOrderLinkerInterface
 * @package Ekyna\Component\Commerce\Stock\Linker
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierOrderLinkerInterface
{
    /**
     * Link the given supplier order item to new stock unit.
     *
     * @throws LogicException
     */
    public function linkItem(SupplierOrderItemInterface $item): void;

    /**
     * Dispatches the ordered quantity change over assignments.
     *
     * @return bool Whether the stock unit has been updated.
     *
     * @throws LogicException
     */
    public function applyItem(SupplierOrderItemInterface $item): bool;

    /**
     * Unlink the given supplier order item from its stock unit.
     *
     * @throws LogicException
     */
    public function unlinkItem(SupplierOrderItemInterface $item): void;

    /**
     * Updates the stock unit data (EDA, net and shipping).
     */
    public function updateData(SupplierOrderItemInterface $item): void;

    public function linkDeliveryItem(SupplierDeliveryItemInterface $item): void;

    public function applyDeliveryItem(SupplierDeliveryItemInterface $item): void;

    public function unlinkDeliveryItem(SupplierDeliveryItemInterface $item): void;
}
