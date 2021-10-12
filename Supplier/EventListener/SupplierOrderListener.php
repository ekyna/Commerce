<?php

namespace Ekyna\Component\Commerce\Supplier\EventListener;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Repository\WarehouseRepositoryInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
use Ekyna\Component\Commerce\Supplier\Updater\SupplierOrderUpdaterInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class SupplierOrderListener
 * @package Ekyna\Component\Commerce\Supplier\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderListener extends AbstractListener
{
    /**
     * @var SupplierOrderUpdaterInterface
     */
    protected $supplierOrderUpdater;

    /**
     * @var WarehouseRepositoryInterface
     */
    protected $warehouseRepository;


    /**
     * Constructor.
     *
     * @param SupplierOrderUpdaterInterface $supplierOrderUpdater
     * @param WarehouseRepositoryInterface  $warehouseRepository
     */
    public function __construct(
        SupplierOrderUpdaterInterface $supplierOrderUpdater,
        WarehouseRepositoryInterface $warehouseRepository
    ) {
        $this->supplierOrderUpdater = $supplierOrderUpdater;
        $this->warehouseRepository = $warehouseRepository;
    }

    /**
     * Initialize event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInitialize(ResourceEventInterface $event)
    {
        $order = $this->getSupplierOrderFromEvent($event);

        if (null !== $supplier = $order->getSupplier()) {
            if ($order->getCurrency() !== $supplier->getCurrency()) {
                $order->setCurrency($supplier->getCurrency());
            }
            if (null === $order->getCarrier()) {
                $order->setCarrier($supplier->getCarrier());
            }
        }

        if (null === $order->getWarehouse()) {
            $order->setWarehouse($this->warehouseRepository->findDefault());
        }
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $order = $this->getSupplierOrderFromEvent($event);

        $changed = $this->supplierOrderUpdater->updateNumber($order);

        $changed |= $this->supplierOrderUpdater->updateState($order);

        $changed |= $this->supplierOrderUpdater->updateTotals($order);

        $changed |= $this->supplierOrderUpdater->updateExchangeRate($order);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($order);
        }
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $order = $this->getSupplierOrderFromEvent($event);

        $changed = $this->supplierOrderUpdater->updateNumber($order);

        $changed |= $this->supplierOrderUpdater->updateState($order);

        $changed |= $this->supplierOrderUpdater->updateTotals($order);

        $changed |= $this->supplierOrderUpdater->updateExchangeRate($order);

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
     *
     * @param ResourceEventInterface $event
     */
    public function onContentChange(ResourceEventInterface $event)
    {
        $order = $this->getSupplierOrderFromEvent($event);

        $changed = $this->supplierOrderUpdater->updateState($order);

        $changed |= $this->supplierOrderUpdater->updateTotals($order);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($order, false);
        }

        $this->updateStockUnits($order);
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        $order = $this->getSupplierOrderFromEvent($event);

        $this->assertDeletable($order);
    }

    /**
     * Updates the stock units.
     *
     * @param SupplierOrderInterface $order
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
     *
     * @param ResourceEventInterface $event
     *
     * @return SupplierOrderInterface
     * @throws InvalidArgumentException
     */
    protected function getSupplierOrderFromEvent(ResourceEventInterface $event)
    {
        $order = $event->getResource();

        if (!$order instanceof SupplierOrderInterface) {
            throw new InvalidArgumentException("Expected instance of " . SupplierOrderInterface::class);
        }

        return $order;
    }
}
