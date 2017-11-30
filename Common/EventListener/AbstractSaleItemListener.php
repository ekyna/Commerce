<?php

namespace Ekyna\Component\Commerce\Common\EventListener;

use Ekyna\Component\Commerce\Common\Builder\AdjustmentBuilderInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
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
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $item = $this->getSaleItemFromEvent($event);

        $change = $this->updateTaxation($item);

        $change |= $this->updateDiscount($item);

        if ($change) {
            $this->persistenceHelper->persistAndRecompute($item);
        }

        $this->scheduleSaleContentChangeEvent($item->getSale());
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
        if ($this->persistenceHelper->isChanged($item, ['taxGroup'])) {
            $change = $this->updateTaxation($item);
        }

        // Handle discount update
        $discountFields = [
            'subjectIdentity.provider', 'subjectIdentity.identifier',
            'netPrice', 'quantity', 'compound', 'private'
        ];
        if ($this->persistenceHelper->isChanged($item, $discountFields)) {
            $change |= $this->updateDiscount($item);
        }

        if ($change) {
            $this->persistenceHelper->persistAndRecompute($item);

            $this->scheduleSaleContentChangeEvent($item->getSale());
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $item = $this->getSaleItemFromEvent($event);

        if (null === $sale = $this->getSaleFromItem($item)) {
            throw new RuntimeException('Failed to retrieve the sale.');
        }

        $this->scheduleSaleContentChangeEvent($sale);
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
        $this->throwIllegalOperationIfItemIsImmutable($event);
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
            throw new IllegalOperationException('ekyna_commerce.sale.message.immutable_element');
        }
    }

    /**
     * Updates the item's discount adjustments.
     *
     * @param Model\SaleItemInterface $item
     *
     * @return bool Whether the adjustments has been changed or not.
     */
    protected function updateDiscount(Model\SaleItemInterface $item)
    {
        return $this->adjustmentBuilder->buildDiscountAdjustmentsForSaleItem($item, true);
    }

    /**
     * Updates the item's taxation adjustments.
     *
     * @param Model\SaleItemInterface $item
     *
     * @return bool Whether the adjustments has been changed or not.
     */
    protected function updateTaxation(Model\SaleItemInterface $item)
    {
        return $this->adjustmentBuilder->buildTaxationAdjustmentsForSaleItem($item, true);
    }

    /**
     * @inheritdoc
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
     * Returns the sale item property path.
     *
     * @return string
     */
    abstract protected function getSalePropertyPath();

    /**
     * Schedules the sale content change event.
     *
     * @param Model\SaleInterface $sale
     */
    abstract protected function scheduleSaleContentChangeEvent(Model\SaleInterface $sale);

    /**
     * Returns the sale item from the resource event.
     *
     * @param ResourceEventInterface $event
     *
     * @return Model\SaleItemInterface
     */
    abstract protected function getSaleItemFromEvent(ResourceEventInterface $event);
}
