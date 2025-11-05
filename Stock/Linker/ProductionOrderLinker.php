<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Linker;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Manufacture\Calculator\ProductionPriceCalculator;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdaterInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class ProductionOrderLinker
 * @package Ekyna\Component\Commerce\Stock\Linker
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionOrderLinker implements ProductionOrderLinkerInterface
{
    public function __construct(
        private readonly StockUnitResolverInterface $unitResolver,
        private readonly StockUnitUpdaterInterface  $stockUnitUpdater,
        private readonly ProductionPriceCalculator  $priceCalculator,
        private readonly PersistenceHelperInterface $persistenceHelper,
    ) {
    }

    public function linkOrder(ProductionOrderInterface $order): void
    {
        if (null !== $order->getStockUnit()) {
            return;
        }
        if (!$order->hasSubjectIdentity()) {
            throw new LogicException('No subject assigned to production order');
        }

        // Find 'unlinked' stock units ordered (+ Cached 'new' stock units look up)
        if (null === $unit = $this->unitResolver->findLinkable($order)) {
            // Not found -> create a new stock unit
            $unit = $this->unitResolver->createBySubjectReference($order);
        }

        $unit
            ->setProductionOrder($order)
            ->setWarehouse($order->getWarehouse());

        $this->stockUnitUpdater->updateOrdered($unit, new Decimal($order->getQuantity()), false);

        $this->updateData($order);
    }

    public function applyOrder(ProductionOrderInterface $order): void
    {
        if (null === $order->getStockUnit()) {
            throw new StockLogicException('Production order has not been linked yet.');
        }

        // Update ordered quantity if needed
        if (!$this->persistenceHelper->isChanged($order, 'quantity')) {
            return;
        }

        // Supplier order item has been previously linked to a stock unit.
        $unit = $order->getStockUnit();

        $this->stockUnitUpdater->updateOrdered($unit, new Decimal($order->getQuantity()), false);
    }

    public function unlinkOrder(ProductionOrderInterface $order): void
    {
        if (null === $unit = $order->getStockUnit()) {
            return;
        }

        $unit
            // Set supplier order item to null
            ->setProductionOrder(null)
            ->setWarehouse(null)
            // Clear calculated data from supplier order item
            ->setNetPrice(new Decimal(0))
            ->setShippingPrice(new Decimal(0))
            ->setEstimatedDateOfArrival(null);

        // Set ordered quantity to zero
        $this->stockUnitUpdater->updateOrdered($unit, new Decimal(0), false);
    }

    public function updateData(ProductionOrderInterface $order): void
    {
        if (!$order->hasSubjectIdentity()) {
            return;
        }

        if (null === $unit = $order->getStockUnit()) {
            return;
        }

        $price = $this->priceCalculator->calculateOrderCost($order);
        $shipping = $this->priceCalculator->calculateShippingCost($order);
        $eda = $order->getEndAt();

        $this->stockUnitUpdater->updateNetPrice($unit, $price);
        $this->stockUnitUpdater->updateShippingPrice($unit, $shipping);
        $this->stockUnitUpdater->updateEstimatedDateOfArrival($unit, $eda);
    }

    public function linkProduction(ProductionInterface $production): void
    {
        if (null === $unit = $production->getProductionOrder()->getStockUnit()) {
            throw new StockLogicException('Production order has not been linked yet.');
        }

        $this->stockUnitUpdater->updateReceived($unit, new Decimal($production->getQuantity()), true);
    }

    public function applyProduction(ProductionInterface $production): void
    {
        if (null === $unit = $production->getProductionOrder()->getStockUnit()) {
            throw new StockLogicException('Production order has not been linked yet.');
        }

        if (!$this->persistenceHelper->isChanged($production, 'quantity')) {
            return;
        }

        $cs = $this->persistenceHelper->getChangeSet($production, 'quantity');

        $quantity = new Decimal($cs[1] - $cs[0]);

        $this->stockUnitUpdater->updateReceived($unit, $quantity, true);
    }

    public function unlinkProduction(ProductionInterface $production): void
    {
        if (null === $unit = $production->getProductionOrder()->getStockUnit()) {
            throw new StockLogicException('Production order has not been linked yet.');
        }

        $cs = $this->persistenceHelper->getChangeSet($production, 'quantity');

        $quantity = empty($cs) ? new Decimal($production->getQuantity()) : new Decimal($cs[0]);

        $this->stockUnitUpdater->updateReceived($unit, $quantity->negate(), true);
    }
}
