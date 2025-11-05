<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Linker;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdaterInterface;
use Ekyna\Component\Commerce\Supplier\Calculator\SupplierOrderItemCalculatorInterface;
use Ekyna\Component\Commerce\Supplier\Event\SupplierDeliveryItemEvents;
use Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryItemInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class SupplierOrderLinker
 * @package Ekyna\Component\Commerce\Stock\Linker
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderLinker implements SupplierOrderLinkerInterface
{
    public function __construct(
        private readonly PersistenceHelperInterface           $persistenceHelper,
        private readonly SupplierOrderItemCalculatorInterface $itemCalculator,
        private readonly StockUnitUpdaterInterface            $stockUnitUpdater,
        private readonly StockUnitResolverInterface           $unitResolver,
    ) {
    }

    public function linkItem(SupplierOrderItemInterface $item): void
    {
        if (null !== $item->getStockUnit()) {
            return;
        }
        if (!$item->hasSubjectIdentity()) {
            return;
        }

        // Find 'unlinked' stock units ordered (+ Cached 'new' stock units look up)
        if (null === $unit = $this->unitResolver->findLinkable($item)) {
            // Not found -> create a new stock unit
            $unit = $this->unitResolver->createBySubjectReference($item);
        }

        $unit
            ->setSupplierOrderItem($item)
            ->setWarehouse($item->getOrder()->getWarehouse());

        $this->stockUnitUpdater->updateOrdered($unit, $item->getSubjectQuantity(), false);

        $this->updateData($item);
    }

    public function applyItem(SupplierOrderItemInterface $item): bool
    {
        if (!$item->hasSubjectIdentity()) {
            return false;
        }

        // Update ordered quantity if needed
        if (!$this->persistenceHelper->isChanged($item, ['quantity', 'packing'])) {
            return false;
        }

        // Supplier order item has been previously linked to a stock unit.
        $unit = $item->getStockUnit();

        $this->stockUnitUpdater->updateOrdered($unit, $item->getSubjectQuantity(), false);

        // If packing changed, update received quantity too (deliveries)
        if ($this->persistenceHelper->isChanged($item, 'packing')) {
            foreach ($item->getDeliveryItems() as $deliveryItem) {
                $this->persistenceHelper->scheduleEvent($deliveryItem, SupplierDeliveryItemEvents::UPDATE);
            }
        }

        return true;
    }

    public function unlinkItem(SupplierOrderItemInterface $item): void
    {
        if (null === $unit = $item->getStockUnit()) {
            return;
        }

        $unit
            // Set supplier order item to null
            ->setSupplierOrderItem(null)
            ->setWarehouse(null)
            // Clear calculated data from supplier order item
            ->setNetPrice(new Decimal(0))
            ->setShippingPrice(new Decimal(0))
            ->setEstimatedDateOfArrival(null);

        // Set ordered quantity to zero
        $this->stockUnitUpdater->updateOrdered($unit, new Decimal(0), false);
    }

    public function updateData(SupplierOrderItemInterface $item): void
    {
        if (!$item->hasSubjectIdentity()) {
            return;
        }

        if (null === $unit = $item->getStockUnit()) {
            return;
        }

        $price = $this->itemCalculator->calculateItemProductPrice($item);
        $shipping = $this->itemCalculator->calculateItemShippingPrice($item);

        $eda = $item->getOrder()->getEstimatedDateOfArrival();

        $this->stockUnitUpdater->updateNetPrice($unit, $price);
        $this->stockUnitUpdater->updateShippingPrice($unit, $shipping);
        $this->stockUnitUpdater->updateEstimatedDateOfArrival($unit, $eda);
    }

    public function linkDeliveryItem(SupplierDeliveryItemInterface $item): void
    {
        // TODO
    }

    public function applyDeliveryItem(SupplierDeliveryItemInterface $item): void
    {
        // TODO
    }

    public function unlinkDeliveryItem(SupplierDeliveryItemInterface $item): void
    {
        // TODO
    }
}
