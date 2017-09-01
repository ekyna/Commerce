<?php

namespace Ekyna\Component\Commerce\Supplier\EventListener;

use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Stock\Linker\StockUnitLinkerInterface;
//use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
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
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var StockUnitLinkerInterface
     */
    protected $stockUnitLinker;

    /**
     * @var StockUnitResolverInterface
     */
    //protected $stockUnitResolver;

    /**
     * @var StockUnitUpdaterInterface
     */
    protected $stockUnitUpdater;


    /**
     * Sets the persistence helper.
     *
     * @param PersistenceHelperInterface $helper
     */
    public function setPersistenceHelper(PersistenceHelperInterface $helper)
    {
        $this->persistenceHelper = $helper;
    }

    /**
     * Sets the stock unit linker.
     *
     * @param StockUnitLinkerInterface $stockUnitLinker
     */
    public function setStockUnitLinker(StockUnitLinkerInterface $stockUnitLinker)
    {
        $this->stockUnitLinker = $stockUnitLinker;
    }

    /**
     * Sets the stock unit resolver.
     *
     * @param StockUnitResolverInterface $resolver
     */
    /*public function setStockUnitResolver(StockUnitResolverInterface $resolver)
    {
        $this->stockUnitResolver = $resolver;
    }*/

    /**
     * Sets the stock unit updater.
     *
     * @param StockUnitUpdaterInterface $updater
     */
    public function setStockUnitUpdater(StockUnitUpdaterInterface $updater)
    {
        $this->stockUnitUpdater = $updater;
    }

    /**
     * Creates the stock unit for the given supplier order item.
     *
     * @param Model\SupplierOrderItemInterface $item
     *
     * @throws Exception\InvalidArgumentException
     * @deprecated
     */
    /*protected function createSupplierOrderItemStockUnit(Model\SupplierOrderItemInterface $item)
    {
        if (null !== $stockUnit = $item->getStockUnit()) {
            if ($stockUnit->getOrderedQuantity() != $item->getQuantity()) {
                throw new Exception\InvalidArgumentException(
                    "Stock unit's ordered quantity does not match the supplier order item quantity."
                );
            }

            return;
        }

        // TODO use stock unit linker

        $stockUnit = $this
            ->stockUnitResolver
            ->createBySupplierOrderItem($item);

        $this->persistenceHelper->persistAndRecompute($stockUnit, true);
        $this->persistenceHelper->persistAndRecompute($item);
    }*/

    /**
     * Deletes the stock unit from the given supplier order item.
     *
     * @param Model\SupplierOrderItemInterface $item
     *
     * @throws Exception\IllegalOperationException
     * @deprecated
     */
    /*protected function deleteSupplierOrderItemStockUnit(Model\SupplierOrderItemInterface $item)
    {
        if (null === $stockUnit = $item->getStockUnit()) {
            return;
        }

        if (0 < $stockUnit->getShippedQuantity()) {
            throw new Exception\IllegalOperationException(
                "Stock unit can't be deleted as it has been partially or fully shipped."
            );
        }

        // TODO use stock unit linker

        $stockUnit->setSupplierOrderItem(null);
        if (0 < $stockUnit->getSoldQuantity()) {
            $this->persistenceHelper->persistAndRecompute($stockUnit);
        } else {
            $this->persistenceHelper->remove($stockUnit, true);
        }

        $this->persistenceHelper->persistAndRecompute($item);
    }*/

    /**
     * Asserts that the resource can be safely deleted.
     *
     * @param ResourceInterface $resource
     *
     * @throws Exception\CommerceExceptionInterface
     */
    protected function assertDeletable(ResourceInterface $resource)
    {
        if ($resource instanceof Model\SupplierOrderItemInterface) {
            $stockUnit = $resource->getStockUnit();
            if (0 < $stockUnit->getShippedQuantity() || 0 < $stockUnit->getSoldQuantity()) {
                throw new Exception\IllegalOperationException(
                    "Supplier delivery can't be removed as at least one ".
                    "of its items is linked to a shipped stock unit or sold order."
                ); // TODO message as translation id
            }
        } elseif ($resource instanceof Model\SupplierOrderInterface) {
            foreach ($resource->getItems() as $item) {
                $this->assertDeletable($item);
            }
        } elseif ($resource instanceof Model\SupplierDeliveryItemInterface) {
            $this->assertDeletable($resource->getOrderItem());
        } elseif ($resource instanceof Model\SupplierDeliveryInterface) {
            foreach ($resource->getItems() as $item) {
                $this->assertDeletable($item);
            }
        } else {
            throw new Exception\InvalidArgumentException("Unexpected resource."); // TODO message as translation id
        }
    }

    /**
     * Schedules the supplier order content change event.
     *
     * @param Model\SupplierOrderInterface $order
     */
    protected function scheduleSupplierOrderContentChangeEvent(Model\SupplierOrderInterface $order)
    {
        $this->persistenceHelper->scheduleEvent(SupplierOrderEvents::CONTENT_CHANGE, $order);
    }
}
