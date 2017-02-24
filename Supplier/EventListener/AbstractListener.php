<?php

namespace Ekyna\Component\Commerce\Supplier\EventListener;

use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdaterInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
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
     * @var StockUnitResolverInterface
     */
    protected $stockUnitResolver;

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
     * Sets the stock unit resolver.
     *
     * @param StockUnitResolverInterface $resolver
     */
    public function setStockUnitResolver(StockUnitResolverInterface $resolver)
    {
        $this->stockUnitResolver = $resolver;
    }

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
     * @param SupplierOrderItemInterface $item
     */
    protected function createSupplierOrderItemStockUnit(SupplierOrderItemInterface $item)
    {
        if (null !== $stockUnit = $item->getStockUnit()) {
            if ($stockUnit->getOrderedQuantity() != $item->getQuantity()) {
                throw new InvalidArgumentException(
                    "Stock unit's ordered quantity does not match the supplier order item quantity."
                );
            }

            return;
        }

        $stockUnit = $this
            ->stockUnitResolver
            ->createBySupplierOrderItem($item);

        $this->persistenceHelper->persistAndRecompute($stockUnit, true);
        $this->persistenceHelper->persistAndRecompute($item);
    }

    /**
     * Deletes the stock unit from the given supplier order item.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @throws IllegalOperationException
     */
    protected function deleteSupplierOrderItemStockUnit(SupplierOrderItemInterface $item)
    {
        if (null === $stockUnit = $item->getStockUnit()) {
            return;
        }

        if (0 < $stockUnit->getShippedQuantity()) {
            throw new IllegalOperationException(
                "Stock unit can't be deleted as it has been partially or fully shipped."
            );
        }

        $stockUnit->setSupplierOrderItem(null);

        $this->persistenceHelper->remove($stockUnit, true);
        $this->persistenceHelper->persistAndRecompute($item);
    }
}
