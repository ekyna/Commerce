<?php

namespace Ekyna\Component\Commerce\Supplier\EventListener;

use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdaterInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
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
     * @var ResourceEventDispatcherInterface
     */
    protected $dispatcher;


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
     * Sets the resource event dispatcher.
     *
     * @param ResourceEventDispatcherInterface $dispatcher
     */
    public function setDispatcher(ResourceEventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Finds the supplier order item's relative stock unit.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface|null
     */
    protected function findStockUnit(SupplierOrderItemInterface $item)
    {
        // Get subject provider
        $provider = $this->stockUnitResolver->getProviderByRelative($item);
        if (null !== $provider) {
            // Get the stock unit repository
            $repository = $provider->getStockUnitRepository();

            // Find the stock unit
            return $repository->findOneBySupplierOrderItem($item);
        }

        return null;
    }

    /**
     * Creates the supplier order item's relative stock unit.
     *
     * @param SupplierOrderItemInterface $item
     */
    protected function createStockUnit(SupplierOrderItemInterface $item)
    {
        // Look for an existing stock unit
        if (null === $stockUnit = $this->findStockUnit($item)) { // TODO greedy ?
            // Get subject provider
            $provider = $this->stockUnitResolver->getProviderByRelative($item);
            if (null === $provider) {
                return;
            }

            // Resolve the subject
            $subject = $provider->resolve($item);

            // Get the stock unit repository
            $repository = $provider->getStockUnitRepository();

            // Create a new stock unit
            $stockUnit = $repository->createNew();

            // Set the subject and supplier order item
            $stockUnit
                ->setSubject($subject)
                ->setSupplierOrderItem($item);
        }

        // Set the ordered quantity and estimated date of arrival
        $stockUnit
            ->setOrderedQuantity($item->getQuantity())
            ->setEstimatedDateOfArrival($item->getOrder()->getEstimatedDateOfArrival());

        $this->persistenceHelper->persistAndRecompute($stockUnit);

        // TODO This should be handled by the persistence helper
        if (null !== $eventName = $this->dispatcher->getResourceEventName($stockUnit, 'insert')) {
            $event = $this->dispatcher->createResourceEvent($stockUnit);
            $this->dispatcher->dispatch($eventName, $event);
        }
    }

    /**
     * Updates the stock unit ordered quantity from given supplier order item.
     *
     * @param SupplierOrderItemInterface $item
     * @param float                      $quantity
     */
    protected function updateOrderedQuantity(SupplierOrderItemInterface $item, $quantity)
    {
        // Find the stock unit
        if (null !== $stockUnit = $this->findStockUnit($item)) {
            // Updates the ordered quantity
            $this->stockUnitUpdater->updateOrdered($stockUnit, $quantity);
        }
    }

    /**
     * Updates the stock unit estimated date of arrival from given supplier order item.
     *
     * @param SupplierOrderItemInterface $item
     * @param \DateTime                  $date
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
     * Returns whether or not the relative stock unit has been shipped to customer(s).
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return bool
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
            // Updates the ordered quantity
            $this->stockUnitUpdater->updateDelivered($stockUnit, $quantity);
        }
    }
}
