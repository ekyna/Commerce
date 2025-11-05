<?php

namespace Ekyna\Component\Commerce\Stock\EventListener;

use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Manufacture\Model\POState;
use Ekyna\Component\Commerce\Stock\Event\StockUnitEvents;
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
    protected PersistenceHelperInterface       $persistenceHelper;
    protected ResourceEventDispatcherInterface $dispatcher;
    protected StockUnitStateResolverInterface  $stateResolver;


    public function setPersistenceHelper(PersistenceHelperInterface $helper): void
    {
        $this->persistenceHelper = $helper;
    }

    public function setDispatcher(ResourceEventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    public function setStateResolver(StockUnitStateResolverInterface $stateResolver): void
    {
        $this->stateResolver = $stateResolver;
    }

    /**
     * Insert event handler.
     */
    public function onInsert(ResourceEventInterface $event): void
    {
        $stockUnit = $this->getStockUnitFromEvent($event);

        if ($this->stateResolver->resolve($stockUnit)) {
            $this->persistenceHelper->persistAndRecompute($stockUnit, false);
        }

        $this->scheduleSubjectStockUnitChangeEvent($stockUnit);
    }

    /**
     * Update event handler.
     */
    public function onUpdate(ResourceEventInterface $event): void
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

        if ($this->persistenceHelper->isChanged($stockUnit, ['netPrice', 'shippingPrice'])) {
            $this->persistenceHelper->scheduleEvent($stockUnit, StockUnitEvents::COST_CHANGE);
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onDelete(ResourceEventInterface $event): void
    {
        $stockUnit = $this->getStockUnitFromEvent($event);

        if (null !== $item = $stockUnit->getSupplierOrderItem()) {
            // Prevent deletion if the supplier order has a stockable state
            if (SupplierOrderStates::isStockableState($item->getOrder())) {
                throw new IllegalOperationException(
                    'The stock unit can\'t be deleted as it is linked to a supplier order with a stockable state.'
                ); // TODO message as translation id
            }
        }

        if (null !== $order = $stockUnit->getProductionOrder()) {
            // Prevent deletion if the supplier order has a stockable state
            if (POState::isStockableState($order)) {
                throw new IllegalOperationException(
                    'The stock unit can\'t be deleted as it is linked to a production order with a stockable state.'
                ); // TODO message as translation id
            }
        }

        if (!$stockUnit->isEmpty()) {
            throw new IllegalOperationException(
                'The stock unit can\'t be deleted as it has been received, adjusted, sold or shipped.'
            ); // TODO message as translation id
        }

        $this->scheduleSubjectStockUnitChangeEvent($stockUnit);
    }

    /**
     * Dispatches the subject's "stock unit change" event.
     */
    protected function scheduleSubjectStockUnitChangeEvent(StockUnitInterface $stockUnit): void
    {
        $this->persistenceHelper->scheduleEvent(
            new SubjectStockUnitEvent($stockUnit),
            $this->getSubjectStockUnitChangeEventName()
        );
    }

    /**
     * Returns the stock unit from the event.
     *
     * @throws InvalidArgumentException
     */
    abstract protected function getStockUnitFromEvent(ResourceEventInterface $event): StockUnitInterface;

    /**
     * Returns the subject's "stock unit change" event name.
     */
    abstract protected function getSubjectStockUnitChangeEventName(): string;
}
