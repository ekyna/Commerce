<?php

namespace Ekyna\Component\Commerce\Common\EventListener;

use Ekyna\Component\Commerce\Common\Builder\AdjustmentBuilderInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractSaleItemListener
 * @package Ekyna\Component\Commerce\Common\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleItemListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var AdjustmentBuilderInterface
     */
    protected $adjustmentBuilder;

    /**
     * @var ResourceEventDispatcherInterface
     */
    protected $dispatcher;


    /**
     * Sets the persistence helper.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function setPersistenceHelper(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * Sets the adjustment builder.
     *
     * @param AdjustmentBuilderInterface $adjustmentBuilder
     */
    public function setAdjustmentBuilder(AdjustmentBuilderInterface $adjustmentBuilder)
    {
        $this->adjustmentBuilder = $adjustmentBuilder;
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
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $item = $this->getSaleItemFromEvent($event);

        if ($this->updateTaxation($item)) {
            $this->persistenceHelper->persistAndRecompute($item);
        }

        $this->dispatchSaleContentChangeEvent($item->getSale());
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $item = $this->getSaleItemFromEvent($event);

        $change = false;

        // Handle taxation update
        if ($this->persistenceHelper->isChanged($item, 'subjectData')) {
            if ($change = $this->updateTaxation($item)) {
                $this->persistenceHelper->persistAndRecompute($item);
            }
        }

        // If net price, quantity or adjustments change : trigger sale content change event
        if ($change || $this->persistenceHelper->isChanged($item, ['netPrice', 'quantity'])) {
            // TODO use event queue
            $this->dispatchSaleContentChangeEvent($item->getSale());
        }
    }

    /**
     * Updates the item's taxation adjustments.
     *
     * @param Model\SaleItemInterface $item
     *
     * @return bool Whether the adjustments has been updated or not.
     */
    protected function updateTaxation(Model\SaleItemInterface $item)
    {
        return $this->adjustmentBuilder->buildTaxationAdjustmentsForSaleItem($item, true);
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $item = $this->getSaleItemFromEvent($event);

        $this->dispatchSaleContentChangeEvent($item->getSale());
    }

    /**
     * Pre update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreUpdate(ResourceEventInterface $event)
    {
        $this->throwIllegalOperationIfItemIsImmutable($event);
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        $this->throwIllegalOperationIfItemIsImmutable($event);
    }

    /**
     * Throws an illegal operation exception if the item is immutable.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    private function throwIllegalOperationIfItemIsImmutable(ResourceEventInterface $event)
    {
        if ($event->getHard()) {
            return;
        }

        $item = $this->getSaleItemFromEvent($event);

        // Stop if item is immutable.
        if ($item->isImmutable()) {
            throw new IllegalOperationException(); // TODO reason message
        }
    }

    /**
     * Dispatches the sale content change event.
     *
     * @param Model\SaleInterface $sale
     */
    abstract protected function dispatchSaleContentChangeEvent(Model\SaleInterface $sale);

    /**
     * Returns the sale item from the resource event.
     *
     * @param ResourceEventInterface $event
     *
     * @return Model\SaleItemInterface
     */
    abstract protected function getSaleItemFromEvent(ResourceEventInterface $event);
}
