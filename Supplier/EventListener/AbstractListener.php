<?php

namespace Ekyna\Component\Commerce\Supplier\EventListener;

use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdaterInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
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

        $stockUnit = $this->stockUnitResolver->createStockUnit($item);

        $stockUnit
            ->setSupplierOrderItem($item)
            ->setOrderedQuantity($item->getQuantity());

        $this->persistenceHelper->persistAndRecompute($stockUnit);
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
    }

    /**
     * Finds the supplier order item's relative stock unit, create if not exists.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface|null
     */
    protected function findStockUnit(SupplierOrderItemInterface $item)
    {
        // Get subject provider
        $provider = $this->stockUnitResolver->getProviderByRelative($item);
        if (null === $provider) {
            return null;
        }
        // Resolve the subject
        $subject = $provider->resolve($item);

        // Get the stock unit repository
        $repository = $provider->getStockUnitRepository();

        // Find the stock unit
        $stockUnit = null;
        if ($item->getId()) {
            if (null === $stockUnit = $repository->findOneBySupplierOrderItem($item)) {
                // Look for 'new' (unassigned) stock units (created manually or by sale shipment items
                // and not linked yet to an order item)
                $stockUnit = $repository->findNewBySubject($subject);
            }

            // TODO Only one stock unit created manually or by a sale shipment item may not be enough.
            // TODO We should fetch an array of stock units.
        }

        if (!$stockUnit) {
            // Get the stock unit repository
            $repository = $provider->getStockUnitRepository();

            // Create a new stock unit
            $stockUnit = $repository->createNew();

            // Set the subject and supplier order item
            $stockUnit
                ->setSubject($subject)
                ->setSupplierOrderItem($item)
                ->setOrderedQuantity($item->getQuantity());

            if ($product = $item->getProduct()) {
                $stockUnit->setEstimatedDateOfArrival($product->getEstimatedDateOfArrival());
            }

            $this->persistenceHelper->persistAndRecompute($stockUnit, true);
        }

        return $stockUnit;
    }

    /**
     * Updates the stock unit ordered quantity from given supplier order item.
     *
     * @param SupplierOrderItemInterface $item
     */
    protected function updateOrderedQuantity(SupplierOrderItemInterface $item)
    {
        // Find the stock unit
        if (null !== $stockUnit = $this->findStockUnit($item)) {
            // Updates the ordered quantity
            $this->stockUnitUpdater->updateOrdered($stockUnit, $item->getQuantity(), false);
        }
    }

    /**
     * Returns whether or not the relative stock unit has been shipped to customer(s).
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return bool
     * @deprecated
     */
    protected function isStockUnitShipped(SupplierOrderItemInterface $item)
    {
        if (null !== $stockUnit = $this->findStockUnit($item)) {
            return 0 < $stockUnit->getShippedQuantity();
        }

        return false;
    }

    /**
     * Updates the stock unit delivered quantity from given supplier order item.
     *
     * @param SupplierOrderItemInterface $item
     * @param float                      $quantity
     */
    protected function updateDeliveredQuantity(SupplierOrderItemInterface $item, $quantity)
    {
        // Find the stock unit
        if (null !== $stockUnit = $this->findStockUnit($item)) {
            // TODO fetch deliveryItems to calculate the quantity (?)

            // Updates the ordered quantity
            $this->stockUnitUpdater->updateDelivered($stockUnit, $quantity);
        }
    }

    /**
     * Updates the stock unit estimated date of arrival from given supplier order item.
     *
     * @param SupplierOrderItemInterface $item
     * @param \DateTime                  $date
     *
     * @deprecated
     */
    protected function updateEstimatedDateOfArrival(SupplierOrderItemInterface $item, \DateTime $date)
    {
        // Find the stock unit
        if (null !== $stockUnit = $this->findStockUnit($item)) {
            // Updates the estimated date of arrival
            $this->stockUnitUpdater->updateEstimatedDateOfArrival($stockUnit, $date);
        }
    }

    /**
     * Schedules the stock unit's delete event.
     *
     * @param StockUnitInterface $stockUnit
     */
    /*protected function scheduleStockUnitDeleteEvent(StockUnitInterface $stockUnit)
    {
        $this->persistenceHelper->remove($stockUnit, true);
    }*/
}
