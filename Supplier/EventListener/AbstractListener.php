<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\EventListener;

use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Stock\Linker\StockUnitLinkerInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdaterInterface;
use Ekyna\Component\Commerce\Supplier\Event\SupplierOrderEvents;
use Ekyna\Component\Commerce\Supplier\Model;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractListener
 * @package Ekyna\Component\Commerce\Supplier\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractListener
{
    protected PersistenceHelperInterface $persistenceHelper;
    protected StockUnitLinkerInterface $stockUnitLinker;
    protected StockUnitUpdaterInterface $stockUnitUpdater;

    public function setPersistenceHelper(PersistenceHelperInterface $helper): void
    {
        $this->persistenceHelper = $helper;
    }

    public function setStockUnitLinker(StockUnitLinkerInterface $stockUnitLinker): void
    {
        $this->stockUnitLinker = $stockUnitLinker;
    }

    public function setStockUnitUpdater(StockUnitUpdaterInterface $updater): void
    {
        $this->stockUnitUpdater = $updater;
    }

    /**
     * Asserts that the resource can be safely deleted.
     *
     * @throws Exception\IllegalOperationException
     * @throws Exception\UnexpectedTypeException
     */
    protected function assertDeletable(ResourceInterface $resource): void
    {
        if ($resource instanceof Model\SupplierOrderItemInterface) {
            if (null === $unit = $resource->getStockUnit()) {
                return;
            }

            if ($unit->getReceivedQuantity() + $unit->getAdjustedQuantity() < $unit->getShippedQuantity()) {
                throw new Exception\IllegalOperationException(
                    "Supplier order item can't be removed as it is linked to a shipped stock unit."
                ); // TODO message as translation id
            }

            return;
        }

        if ($resource instanceof Model\SupplierOrderInterface) {
            foreach ($resource->getItems() as $item) {
                $this->assertDeletable($item);
            }

            return;
        }

        if ($resource instanceof Model\SupplierDeliveryItemInterface) {
            $this->assertDeletable($resource->getOrderItem());

            return;
        }

        if ($resource instanceof Model\SupplierDeliveryInterface) {
            foreach ($resource->getItems() as $item) {
                $this->assertDeletable($item);
            }

            return;
        }


        throw new Exception\UnexpectedTypeException($resource, [
            Model\SupplierOrderItemInterface::class,
            Model\SupplierOrderInterface::class,
            Model\SupplierDeliveryItemInterface::class,
            Model\SupplierDeliveryInterface::class
        ]);
    }

    /**
     * Schedules the supplier order content change event.
     */
    protected function scheduleSupplierOrderContentChangeEvent(Model\SupplierOrderInterface $order): void
    {
        $this->persistenceHelper->scheduleEvent($order, SupplierOrderEvents::CONTENT_CHANGE);
    }
}
