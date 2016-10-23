<?php

namespace Ekyna\Component\Commerce\Common\EventListener;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractAdjustmentListener
 * @package Ekyna\Component\Commerce\Common\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAdjustmentListener
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
     * Sets the persistence helper.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function setPersistenceHelper(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
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
        $adjustment = $this->getAdjustmentFromEvent($event);

        $this->dispatchSaleContentChangeEvent($adjustment);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $adjustment = $this->getAdjustmentFromEvent($event);

        // TODO only if amount, mode or type changed ?
        if ($this->persistenceHelper->isChanged($adjustment, ['amount', 'mode', 'type'])) {
            $this->dispatchSaleContentChangeEvent($adjustment);
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $adjustment = $this->getAdjustmentFromEvent($event);

        $this->dispatchSaleContentChangeEvent($adjustment);
    }

    /**
     * Pre update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreUpdate(ResourceEventInterface $event)
    {
        $this->throwIllegalOperationIfAdjustmentIsImmutable($event);
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        $this->throwIllegalOperationIfAdjustmentIsImmutable($event);
    }

    /**
     * Throws an illegal operation exception if the adjustment is immutable.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    private function throwIllegalOperationIfAdjustmentIsImmutable(ResourceEventInterface $event)
    {
        if ($event->getHard()) {
            return;
        }

        $adjustment = $this->getAdjustmentFromEvent($event);

        // Stop if adjustment is immutable.
        if ($adjustment->isImmutable()) {
            throw new IllegalOperationException(); // TODO reason message
        }
    }

    /**
     * Dispatches the sale content change event.
     *
     * @param Model\AdjustmentInterface $adjustment
     */
    abstract protected function dispatchSaleContentChangeEvent(Model\AdjustmentInterface $adjustment);

    /**
     * Returns the adjustment from the resource event.
     *
     * @param ResourceEventInterface $event
     *
     * @return Model\AdjustmentInterface
     */
    abstract protected function getAdjustmentFromEvent(ResourceEventInterface $event);
}
