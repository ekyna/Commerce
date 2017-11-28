<?php

namespace Ekyna\Component\Commerce\Common\EventListener;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
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
     * Sets the persistence helper.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function setPersistenceHelper(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $adjustment = $this->getAdjustmentFromEvent($event);

        $this->scheduleSaleContentChangeEvent($adjustment);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $adjustment = $this->getAdjustmentFromEvent($event);

        if ($this->persistenceHelper->isChanged($adjustment, ['amount', 'mode', 'type'])) {
            $this->scheduleSaleContentChangeEvent($adjustment);
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

        $this->scheduleSaleContentChangeEvent($adjustment);
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
            throw new IllegalOperationException('ekyna_commerce.sale.message.immutable_element');
        }
    }

    /**
     * Dispatches the sale content change event.
     *
     * @param Model\AdjustmentInterface $adjustment
     */
    protected function scheduleSaleContentChangeEvent(Model\AdjustmentInterface $adjustment)
    {
        if ($adjustment instanceof Model\SaleAdjustmentInterface) {
            if (null === $sale = $this->getSaleFromAdjustment($adjustment)) {
                throw new RuntimeException("Failed to retrieve the sale.");
            }
        } elseif ($adjustment instanceof Model\SaleItemAdjustmentInterface) {
            if (null === $item = $this->getItemFromAdjustment($adjustment)) {
                throw new RuntimeException("Failed to retrieve the sale item.");
            }
            if (null === $sale = $this->getSaleFromItem($item)) {
                throw new RuntimeException("Failed to retrieve the sale.");
            }
        } else {
            throw new InvalidArgumentException("Unexpected adjustment type.");
        }

        $this->persistenceHelper->scheduleEvent($this->getSaleChangeEvent(), $sale);
    }

    /**
     * Returns the sale item from the given sale item adjustment (looking into change set).
     *
     * @param Model\SaleItemAdjustmentInterface $adjustment
     *
     * @return Model\SaleItemInterface|null
     */
    protected function getItemFromAdjustment(Model\SaleItemAdjustmentInterface $adjustment)
    {
        if (null !== $item = $adjustment->getItem()) {
            return $item;
        }

        $cs = $this->persistenceHelper->getChangeSet($adjustment, 'item');

        if (!empty($cs)) {
            return $cs[0];
        }

        return null;
    }

    /**
     * Returns the sale from the given sale item (looking into change set).
     *
     * @param Model\SaleItemInterface $item
     *
     * @return Model\SaleInterface|null
     */
    protected function getSaleFromItem(Model\SaleItemInterface $item)
    {
        if (null !== $sale = $item->getSale()) {
            return $sale;
        }

        $path = $this->getSalePropertyPath();
        $cs = $this->persistenceHelper->getChangeSet($item);

        if (isset($cs[$path])) {
            return $cs[$path][0];
        } elseif (null !== $parent = $item->getParent()) {
            return $this->getSaleFromItem($parent);
        } elseif (isset($cs['parent'])) {
            return $this->getSaleFromItem($cs['parent'][0]);
        }

        return null;
    }

    /**
     * Returns the sale from the given sale adjustment (looking into change set).
     *
     * @param Model\SaleAdjustmentInterface $adjustment
     *
     * @return Model\SaleInterface|null
     */
    protected function getSaleFromAdjustment(Model\SaleAdjustmentInterface $adjustment)
    {
        if (null !== $sale = $adjustment->getSale()) {
            return $sale;
        }

        $cs = $this->persistenceHelper->getChangeSet($adjustment, $this->getSalePropertyPath());

        if (!empty($cs)) {
            return $cs[0];
        }

        return null;
    }

    /**
     * Returns the sale item property path.
     *
     * @return string
     */
    abstract protected function getSalePropertyPath();

    /**
     * Returns the sale change event name.
     *
     * @return string
     */
    abstract protected function getSaleChangeEvent();

    /**
     * Returns the adjustment from the resource event.
     *
     * @param ResourceEventInterface $event
     *
     * @return Model\AdjustmentInterface
     */
    abstract protected function getAdjustmentFromEvent(ResourceEventInterface $event);
}
