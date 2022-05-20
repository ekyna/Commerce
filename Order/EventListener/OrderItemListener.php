<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleItemListener;
use Ekyna\Component\Commerce\Common\Helper\QuantityChangeHelper;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Message\OrderItemAdd;
use Ekyna\Component\Commerce\Order\Message\OrderItemQuantityChange;
use Ekyna\Component\Commerce\Order\Message\OrderItemSubjectChange;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Stock\Assigner\StockUnitAssignerInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Message\MessageQueueAwareTrait;

use function array_fill;

/**
 * Class OrderItemListener
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItemListener extends AbstractSaleItemListener
{
    use MessageQueueAwareTrait;

    private StockUnitAssignerInterface $stockAssigner;

    public function setStockAssigner(StockUnitAssignerInterface $stockAssigner): void
    {
        $this->stockAssigner = $stockAssigner;
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        parent::onInsert($event);

        $item = $this->getSaleItemFromEvent($event);

        // If order is in stockable state
        if (OrderStates::isStockableState($item->getRootSale()->getState())) {
            $this->stockAssigner->assignSaleItem($item);

            $this->messageQueue->addMessage(function () use ($item) {
                $identity = $item->getSubjectIdentity();

                return new OrderItemAdd(
                    $item->getId(),
                    $item->getQuantity()->toFixed(5),
                    $identity->getProvider(),
                    $identity->getIdentifier()
                );
            });

            return;
        }

        $this->stockAssigner->detachSaleItem($item);
    }

    public function onUpdate(ResourceEventInterface $event): void
    {
        parent::onUpdate($event);

        $item = $this->getSaleItemFromEvent($event);

        if (!$this->persistenceHelper->isChanged($item, ['quantity', 'subjectIdentity.provider', 'subjectIdentity.identifier'])) {
            return;
        }

        $sale = $item->getRootSale();

        // If sale state has changed
        if ($this->persistenceHelper->isChanged($sale, 'state')) {
            $stateCs = $this->persistenceHelper->getChangeSet($sale, 'state');

            // If order just did a stockable state transition
            if (
                OrderStates::hasChangedToStockable($stateCs)
                || OrderStates::hasChangedFromStockable($stateCs)
            ) {
                // Prevent assignments update (done by the order listener)
                return;
            }
        }

        // If sale released flag has changed
        if ($sale->isSample() && $this->persistenceHelper->isChanged($sale, 'released')) {
            // Prevent assignments update (done by the order listener)
            return;
        }

        // If order is in stockable state and order item quantity has changed
        if (OrderStates::isStockableState($sale->getState())) {
            $this->applySaleItemRecursively($item);
        }
    }

    public function onDelete(ResourceEventInterface $event): void
    {
        parent::onDelete($event);

        $item = $this->getSaleItemFromEvent($event);

        if ($item->hasStockAssignments()) {
            $this->stockAssigner->detachSaleItem($item);
        }
    }

    /**
     * Applies the sale item to stock units recursively.
     */
    protected function applySaleItemRecursively(Model\SaleItemInterface $item): void
    {
        // If subject has changed
        if ($this->persistenceHelper->isChanged($item, ['subjectIdentity.provider', 'subjectIdentity.identifier'])) {
            $this->stockAssigner->detachSaleItem($item);
            $this->stockAssigner->assignSaleItem($item);

            $this->enqueueSubjectChangeMessage($item);
        } else {
            $this->stockAssigner->applySaleItem($item);

            $this->enqueueQuantityChangeMessage($item);
        }

        foreach ($item->getChildren() as $child) {
            if (
                $this->persistenceHelper->isScheduledForInsert($child)
                || (
                    $this->persistenceHelper->isScheduledForUpdate($child)
                    && $this->persistenceHelper->isChanged($child, ['quantity', 'subjectIdentity.provider', 'subjectIdentity.identifier'])
                )
            ) {
                // Skip this item as the listener will be called on it.
                /** @see OrderItemListener::onUpdate() */
                continue;
            }

            $this->applySaleItemRecursively($child);
        }
    }

    private function enqueueSubjectChangeMessage(Model\SaleItemInterface $item): void
    {
        $providers = $this->persistenceHelper->getChangeSet($item, 'subjectIdentity.provider');
        $identifiers = $this->persistenceHelper->getChangeSet($item, 'subjectIdentity.identifier');

        if (empty($providers) && empty($identifiers)) {
            throw new LogicException('Unchanged order item subject.');
        }

        if (empty($providers)) {
            $providers = array_fill(0, 2, $item->getSubjectIdentity()->getProvider());
        }
        if (empty($identifiers)) {
            $identifiers = array_fill(0, 2, $item->getSubjectIdentity()->getIdentifier());
        }

        $helper = new QuantityChangeHelper($this->persistenceHelper);
        $quantities = $helper->getTotalQuantityChangeSet($item);
        if (empty($quantities)) {
            $quantities = array_fill(0, 2, $item->getTotalQuantity());
        }

        $message = new OrderItemSubjectChange($item->getId(), $quantities[0]->toFixed(5), $quantities[1]->toFixed(5));
        $message->setFromSubject($providers[0], $identifiers[0]);
        $message->setToSubject($providers[1], $identifiers[1]);

        $this->messageQueue->addMessage($message);
    }

    private function enqueueQuantityChangeMessage(Model\SaleItemInterface $item): void
    {
        $helper = new QuantityChangeHelper($this->persistenceHelper);
        $quantities = $helper->getTotalQuantityChangeSet($item);

        if (empty($quantities)) {
            throw new LogicException('Unchanged order item quantities.');
        }

        $message = new OrderItemQuantityChange($item->getId(), $quantities[0]->toFixed(5), $quantities[1]->toFixed(5));

        $this->messageQueue->addMessage($message);
    }

    /**
     * @inheritDoc
     */
    protected function getSalePropertyPath(): string
    {
        return 'order';
    }

    /**
     * @inheritDoc
     */
    protected function scheduleSaleContentChangeEvent(Model\SaleInterface $sale): void
    {
        $this->persistenceHelper->scheduleEvent($sale, OrderEvents::CONTENT_CHANGE);
    }

    /**
     * @inheritDoc
     *
     * @return OrderItemInterface
     */
    protected function getSaleItemFromEvent(ResourceEventInterface $event): Model\SaleItemInterface
    {
        $item = $event->getResource();

        if (!$item instanceof OrderItemInterface) {
            throw new UnexpectedTypeException($item, OrderItemInterface::class);
        }

        return $item;
    }
}
