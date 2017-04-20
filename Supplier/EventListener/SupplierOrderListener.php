<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\EventListener;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
use Ekyna\Component\Commerce\Supplier\Updater\SupplierOrderUpdaterInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;

/**
 * Class SupplierOrderListener
 * @package Ekyna\Component\Commerce\Supplier\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderListener extends AbstractListener
{
    protected SupplierOrderUpdaterInterface $supplierOrderUpdater;


    public function __construct(SupplierOrderUpdaterInterface $supplierOrderUpdater)
    {
        $this->supplierOrderUpdater = $supplierOrderUpdater;
    }

    /**
     * Insert event handler.
     */
    public function onInsert(ResourceEventInterface $event): void
    {
        $order = $this->getSupplierOrderFromEvent($event);

        $changed = $this->supplierOrderUpdater->updateNumber($order);

        $changed = $this->supplierOrderUpdater->updateState($order) || $changed;

        $changed = $this->supplierOrderUpdater->updateTotals($order) || $changed;

        $changed = $this->supplierOrderUpdater->updateExchangeRate($order) || $changed;

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($order);
        }
    }

    /**
     * Update event handler.
     */
    public function onUpdate(ResourceEventInterface $event): void
    {
        $order = $this->getSupplierOrderFromEvent($event);

        $changed = $this->supplierOrderUpdater->updateNumber($order);

        $changed = $this->supplierOrderUpdater->updateState($order) || $changed;

        $changed = $this->supplierOrderUpdater->updateTotals($order) || $changed;

        $changed = $this->supplierOrderUpdater->updateExchangeRate($order) || $changed;

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($order, false);
        }

        // Deletable <=> Stockable state change case.
        if ($this->persistenceHelper->isChanged($order, 'state')) {
            $stateCs = $this->persistenceHelper->getChangeSet($order, 'state');

            // If order's state has changed to a non stockable state
            if (SupplierOrderStates::hasChangedFromStockable($stateCs)) {
                // Delete stock unit (if exists) for each supplier order items.
                foreach ($order->getItems() as $item) {
                    $this->stockUnitLinker->unlinkItem($item);
                }
            } // Else if order state's has changed to a stockable state
            elseif (SupplierOrderStates::hasChangedToStockable($stateCs)) {
                // Create stock unit (if not exists) for each supplier order items.
                foreach ($order->getItems() as $item) {
                    $this->stockUnitLinker->linkItem($item);
                }
            }

            return;
        }

        $this->updateStockUnits($order);
    }

    /**
     * Content change event handler.
     */
    public function onContentChange(ResourceEventInterface $event): void
    {
        $order = $this->getSupplierOrderFromEvent($event);

        $changed = $this->supplierOrderUpdater->updateState($order);

        $changed = $this->supplierOrderUpdater->updateTotals($order) || $changed;

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($order, false);
        }

        $this->updateStockUnits($order);
    }

    /**
     * Pre delete event handler.
     */
    public function onPreDelete(ResourceEventInterface $event): void
    {
        $order = $this->getSupplierOrderFromEvent($event);

        $this->assertDeletable($order);
    }

    /**
     * Updates the stock units.
     */
    protected function updateStockUnits(SupplierOrderInterface $order): void
    {
        if (!$this->isStockUnitDataUpdateNeeded($order)) {
            return;
        }

        foreach ($order->getItems() as $item) {
            $this->stockUnitLinker->updateData($item);
        }
    }

    private function isStockUnitDataUpdateNeeded(SupplierOrderInterface $order): bool
    {
        if (!SupplierOrderStates::isStockableState($order->getState())) {
            return false;
        }

        $properties = [
            'discountTotal',
            'shippingCost',
            'forwarderFee',
            'customsTax',
            'exchangeRate',
            'estimatedDateOfArrival',
            // TODO 'totalWeight'
        ];

        if ($this->persistenceHelper->isChanged($order, $properties)) {
            return true;
        }

        foreach ($order->getItems() as $item) {
            if ($this->persistenceHelper->isChanged($item, ['quantity', 'netPrice', 'weight'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the supplier order from the event.
     */
    protected function getSupplierOrderFromEvent(ResourceEventInterface $event): SupplierOrderInterface
    {
        $order = $event->getResource();

        if (!$order instanceof SupplierOrderInterface) {
            throw new UnexpectedTypeException($order, SupplierOrderInterface::class);
        }

        return $order;
    }
}
