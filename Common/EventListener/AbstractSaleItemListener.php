<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\EventListener;

use Ekyna\Component\Commerce\Common\Builder\AdjustmentBuilderInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractSaleItemListener
 * @package Ekyna\Component\Commerce\Common\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleItemListener
{
    protected PersistenceHelperInterface $persistenceHelper;
    protected AdjustmentBuilderInterface $adjustmentBuilder;

    public function setPersistenceHelper(PersistenceHelperInterface $persistenceHelper): void
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    public function setAdjustmentBuilder(AdjustmentBuilderInterface $adjustmentBuilder): void
    {
        $this->adjustmentBuilder = $adjustmentBuilder;
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        $item = $this->getSaleItemFromEvent($event);

        $changed = $this->updateTaxation($item);

        $changed = $this->updateDiscount($item) || $changed;

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($item, false);
        }

        $this->scheduleSaleContentChangeEvent($item->getRootSale());
    }

    public function onUpdate(ResourceEventInterface $event): void
    {
        $item = $this->getSaleItemFromEvent($event);

        $change = false;

        // Handle taxation update
        if ($this->persistenceHelper->isChanged($item, ['taxGroup'])) {
            $change = $this->updateTaxation($item);
        }

        // Handle discount update
        $discountFields = [
            'subjectIdentity.provider', 'subjectIdentity.identifier',
            'netPrice', 'quantity', 'compound', 'private'
        ];
        if ($this->persistenceHelper->isChanged($item, $discountFields)) {
            $this->updateDiscount($item);
            $change = true;
        }

        if ($change) {
            $this->persistenceHelper->persistAndRecompute($item, false);

            $this->scheduleSaleContentChangeEvent($item->getRootSale());
        }
    }

    public function onDelete(ResourceEventInterface $event): void
    {
        $item = $this->getSaleItemFromEvent($event);

        // Sale may be null here: if it is schedule for delete,
        // the persistence tracker won't be able to retrieve it.
        $sale = $this->getSaleFromItem($item);

        if (null !== $parent = $item->getParent()) {
            $parent->removeChild($item);
        } elseif (null !== $sale) {
            $sale->removeItem($item);
        }

        if (null !== $sale) {
            $this->scheduleSaleContentChangeEvent($sale);
        }
    }

    public function onPreUpdate(ResourceEventInterface $event): void
    {
        // TODO Disabled to allow admins to change weight and net price
        // $this->throwIllegalOperationIfItemIsImmutable($event);
    }

    public function onPreDelete(ResourceEventInterface $event): void
    {
        $this->throwIllegalOperationIfItemIsImmutable($event);

        $item = $this->getSaleItemFromEvent($event);

        if (null === $sale = $item->getRootSale()) {
            return;
        }

        $items = $sale->getItems()->toArray();
        foreach ($items as $item) {
            $this->loadItem($item);
        }
    }

    /**
     * Loads the item's children and adjustments recursively.
     */
    private function loadItem(Model\SaleItemInterface $item): void
    {
        $item->getAdjustments()->toArray();

        $children = $item->getChildren()->toArray();

        foreach ($children as $child) {
            $this->loadItem($child);
        }
    }

    /**
     * Throws an illegal operation exception if the item is immutable.
     */
    private function throwIllegalOperationIfItemIsImmutable(ResourceEventInterface $event): void
    {
        if ($event->getHard()) {
            return;
        }

        $item = $this->getSaleItemFromEvent($event);

        // Stop if item is immutable.
        if ($item->isImmutable()) {
            throw new IllegalOperationException('sale.message.immutable_element');
        }
    }

    /**
     * Updates the item's discount adjustments.
     *
     * @return bool Whether the adjustments have been changed or not.
     */
    protected function updateDiscount(Model\SaleItemInterface $item): bool
    {
        return $this->adjustmentBuilder->buildSaleItemDiscountAdjustments($item, true);
    }

    /**
     * Updates the item's taxation adjustments.
     *
     * @return bool Whether the adjustments have been changed or not.
     */
    protected function updateTaxation(Model\SaleItemInterface $item): bool
    {
        return $this->adjustmentBuilder->buildSaleItemTaxationAdjustments($item, true);
    }

    /**
     * Returns the item's sale.
     *
     * @param Model\SaleItemInterface $item
     *
     * @return Model\SaleInterface
     */
    protected function getSaleFromItem(Model\SaleItemInterface $item): ?Model\SaleInterface
    {
        if (null !== $sale = $item->getRootSale()) {
            return $sale;
        }

        $path = $this->getSalePropertyPath();
        $cs = $this->persistenceHelper->getChangeSet($item);

        if (isset($cs[$path], $cs[$path][0])) {
            return $cs[$path][0];
        } elseif (null !== $parent = $item->getParent()) {
            return $this->getSaleFromItem($parent);
        } elseif (isset($cs['parent'], $cs['parent'][0])) {
            return $this->getSaleFromItem($cs['parent'][0]);
        }

        return null;
    }

    /**
     * Returns the sale item property path.
     *
     * @return string
     */
    abstract protected function getSalePropertyPath(): string;

    /**
     * Schedules the sale content change event.
     *
     * @param Model\SaleInterface $sale
     */
    abstract protected function scheduleSaleContentChangeEvent(Model\SaleInterface $sale): void;

    /**
     * Returns the sale item from the resource event.
     *
     * @param ResourceEventInterface $event
     *
     * @return Model\SaleItemInterface
     */
    abstract protected function getSaleItemFromEvent(ResourceEventInterface $event): Model\SaleItemInterface;
}
