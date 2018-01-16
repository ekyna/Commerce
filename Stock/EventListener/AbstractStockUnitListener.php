<?php

namespace Ekyna\Component\Commerce\Stock\EventListener;

use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Event\SubjectStockUnitEvent;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitStateResolverInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractStockUnitListener
 * @package Ekyna\Component\Commerce\Stock\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractStockUnitListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var ResourceEventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var StockUnitStateResolverInterface
     */
    protected $stateResolver;


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
     * Sets the resource event dispatcher.
     *
     * @param ResourceEventDispatcherInterface $dispatcher
     */
    public function setDispatcher(ResourceEventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Sets the stock unit state resolver.
     *
     * @param StockUnitStateResolverInterface $stateResolver
     */
    public function setStateResolver(StockUnitStateResolverInterface $stateResolver)
    {
        $this->stateResolver = $stateResolver;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $stockUnit = $this->getStockUnitFromEvent($event);

        if ($this->stateResolver->resolve($stockUnit)) {
            $this->persistenceHelper->persistAndRecompute($stockUnit, false);
        }

        $this->scheduleSubjectStockUnitChangeEvent($stockUnit);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $stockUnit = $this->getStockUnitFromEvent($event);

        $trackProperties = [
            'state',
            'orderedQuantity',
            'receivedQuantity',
            'adjustedQuantity',
            'soldQuantity',
            'shippedQuantity',
            'estimatedDateOfArrival'
        ];

        if ($this->persistenceHelper->isChanged($stockUnit, $trackProperties)) {
            if ($this->stateResolver->resolve($stockUnit)) {
                $this->persistenceHelper->persistAndRecompute($stockUnit, false);
            }

            $this->scheduleSubjectStockUnitChangeEvent($stockUnit);
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $stockUnit = $this->getStockUnitFromEvent($event);

        if (null !== $item = $stockUnit->getSupplierOrderItem()) {
            // Prevent deletion if the supplier order has a stockable state
            if (SupplierOrderStates::isStockableState($item->getOrder()->getState())) {
                throw new IllegalOperationException(
                    "The stock unit can't be deleted as it is linked to a supplier order with a stockable state."
                ); // TODO message as translation id
            }
        }

        if (!$stockUnit->isEmpty()) {
            throw new IllegalOperationException(
                "The stock unit can't be deleted as it has been received, adjusted, sold or shipped."
            ); // TODO message as translation id
        }

        $this->scheduleSubjectStockUnitChangeEvent($stockUnit);
    }

    /**
     * Dispatches the subject's "stock unit change" event.
     *
     * @param StockUnitInterface $stockUnit
     */
    protected function scheduleSubjectStockUnitChangeEvent(StockUnitInterface $stockUnit)
    {
        $this->persistenceHelper->scheduleEvent(
            $this->getSubjectStockUnitChangeEventName(),
            new SubjectStockUnitEvent($stockUnit)
        );
    }

    /**
     * Returns the stock unit from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return StockUnitInterface
     * @throws InvalidArgumentException
     */
    abstract protected function getStockUnitFromEvent(ResourceEventInterface $event);

    /**
     * Returns the subject's "stock unit change" event name.
     *
     * @return string
     */
    abstract protected function getSubjectStockUnitChangeEventName();
}
