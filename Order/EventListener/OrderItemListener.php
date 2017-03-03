<?php

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleItemListener;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Stock\Assigner\StockUnitAssignerInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderItemListener
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItemListener extends AbstractSaleItemListener
{
    /**
     * @var StockUnitAssignerInterface
     */
    protected $stockAssigner;


    /**
     * Sets the stock assigner.
     *
     * @param StockUnitAssignerInterface $stockAssigner
     */
    public function setStockAssigner(StockUnitAssignerInterface $stockAssigner)
    {
        $this->stockAssigner = $stockAssigner;
    }

    /**
     * @inheritdoc
     */
    public function onInsert(ResourceEventInterface $event)
    {
        parent::onInsert($event);

        // TODO
        // If order is in stockable state
        //   $this->stockAssigner->createAssignments($item);
        // Else if order is at a deletable state
        //   $this->stockAssigner->removeAssignments($item);
    }

    /**
     * @inheritdoc
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        parent::onUpdate($event);

        // TODO
        // If order state has changed from deletable to stockable or from stockable to deletable
        //   -> abort (stock assignments operation has been made at order level)

        // TODO (in a dedicated service : handleSaleItemUpdate ?)
        // If order is in stockable state and order item quantity has changed
        //   $this->stockAssigner->dispatchQuantityChange($item, $deltaQuantity);
    }

    /**
     * @inheritdoc
     */
    public function onDelete(ResourceEventInterface $event)
    {
        parent::onDelete($event);

        // TODO (in a dedicated service : handleSaleItemRemove ?)
        // If order is in stockable state
        //   $this->stockAssigner->removeAssignments($item);
    }

    /**
     * Pre create event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onPreCreate(ResourceEventInterface $event)
    {
        if ($event->getHard()) {
            return;
        }

        //$this->throwIllegalOperationIfOrderIsCompleted($event);
    }

    /**
     * Pre update event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onPreUpdate(ResourceEventInterface $event)
    {
        if ($event->getHard()) {
            return;
        }

        parent::onPreUpdate($event);

        //$this->throwIllegalOperationIfOrderIsCompleted($event);
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        if ($event->getHard()) {
            return;
        }

        parent::onPreDelete($event);

        //$this->throwIllegalOperationIfOrderIsCompleted($event);
    }

    /**
     * Throws an illegal operation exception if the order is completed.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
//    private function throwIllegalOperationIfOrderIsCompleted(ResourceEventInterface $event)
//    {
//        $item = $this->getSaleItemFromEvent($event);
//        /** @var \Ekyna\Component\Commerce\Order\Model\OrderInterface $order */
//        $order = $item->getSale();
//
//        // Stop sale is completed.
//        if ($order->getState() === OrderStates::STATE_COMPLETED) {
//            throw new IllegalOperationException(); // TODO reason message
//        }
//    }

    /**
     * @inheritdoc
     */
    protected function scheduleSaleContentChangeEvent(Model\SaleInterface $sale)
    {
        $this->persistenceHelper->scheduleEvent(OrderEvents::CONTENT_CHANGE, $sale);
    }

    /**
     * @inheritdoc
     */
    protected function getSaleItemFromEvent(ResourceEventInterface $event)
    {
        $item = $event->getResource();

        if (!$item instanceof OrderItemInterface) {
            throw new InvalidArgumentException("Expected instance of OrderItemInterface");
        }

        return $item;
    }
}
