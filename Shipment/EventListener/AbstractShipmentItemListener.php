<?php

namespace Ekyna\Component\Commerce\Shipment\EventListener;

use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Shipment\Model;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdaterInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractShipmentItemListener
 * @package Ekyna\Component\Commerce\Shipment\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractShipmentItemListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var StockUnitResolverInterface
     */
    protected $stockUnitResolver;

    /**
     * @var StockUnitUpdaterInterface
     */
    protected $stockUnitUpdater;


    /**
     * Sets the persistence helper.
     *
     * @param PersistenceHelperInterface $helper
     */
    public function setPersistenceHelper(PersistenceHelperInterface $helper)
    {
        $this->persistenceHelper = $helper;
    }

    /**
     * Sets the stock unit resolver.
     *
     * @param StockUnitResolverInterface $resolver
     */
    public function setStockUnitResolver(StockUnitResolverInterface $resolver)
    {
        $this->stockUnitResolver = $resolver;
    }

    /**
     * Sets the stock unit updater.
     *
     * @param StockUnitUpdaterInterface $updater
     */
    public function setStockUnitUpdater(StockUnitUpdaterInterface $updater)
    {
        $this->stockUnitUpdater = $updater;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $item = $this->getShipmentItemFromEvent($event);

        // If supplier order state is 'ordered', 'partial' or 'completed'
        if (Model\ShipmentStates::isStockState($item->getShipment()->getState())) {
            // Associated stock unit (if not exists) must be created (absolute ordered quantity).
            $this->createSupplierOrderItemStockUnit($item);
        } else { // Supplier order state is 'new' or 'cancelled'
            // Associated stock unit (if exists) must be deleted.
            $this->deleteSupplierOrderItemStockUnit($item);
        }

        if ($this->isShipmentInDebitStockState($item->getShipment())) {
            $this->updateShipped($item, $item->getQuantity());
        }

        $this->scheduleShipmentContentChangeEvent($item->getShipment());
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $shipmentItem = $this->getShipmentItemFromEvent($event);

        // TODO Abort on shipment state transition (deletable <=> stockable)

        $changeSet = $this->persistenceHelper->getChangeSet($shipmentItem);
        if (isset($changeSet['quantity'])) {
            if ($this->isShipmentInDebitStockState($shipmentItem->getShipment())) {
                $this->updateShipped($shipmentItem, $changeSet['quantity'][1] - $changeSet['quantity'][0]);
            }

            $this->scheduleShipmentContentChangeEvent($shipmentItem->getShipment());
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $shipmentItem = $this->getShipmentItemFromEvent($event);

        // TODO test this
        if ($this->isShipmentInDebitStockState($shipmentItem->getShipment())) {
            $this->updateShipped($shipmentItem, -$shipmentItem->getQuantity());
        }

        $this->scheduleShipmentContentChangeEvent($shipmentItem->getShipment());
    }

    /**
     * Pre update event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws Exception\IllegalOperationException
     */
    public function onPreUpdate(ResourceEventInterface $event)
    {
        /*$shipmentItem = $this->getShipmentItemFromEvent($event);

        $shipment = $shipmentItem->getShipment();
        // TODO assert updatable state
        if (!in_array($shipment->getState(), ShipmentStates::getDeletableStates())) {
            throw new IllegalOperationException();
        }*/
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws Exception\IllegalOperationException
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        $shipmentItem = $this->getShipmentItemFromEvent($event);

        $shipment = $shipmentItem->getShipment();

        if (!in_array($shipment->getState(), Model\ShipmentStates::getDeletableStates())) {
            throw new Exception\IllegalOperationException();
        }
    }

    /**
     * Returns whether the shipment is in debit state.
     *
     * @param Model\ShipmentInterface $shipment
     *
     * @return bool
     */
    protected function isShipmentInDebitStockState(Model\ShipmentInterface $shipment)
    {
        $shipmentCS = $this->persistenceHelper->getChangeSet($shipment);
        $shipmentState = isset($shipmentCS['state']) ? $shipmentCS['state'][0] : $shipment->getState();

        // TODO Use constant method
        return in_array($shipmentState, Model\ShipmentStates::getStockStates());
    }

    /**
     * Updates the related stock and persist it if needed.
     *
     * @param Model\ShipmentItemInterface $item
     * @param float                 $quantity
     */
    protected function updateShipped(Model\ShipmentItemInterface $item, $quantity)
    {
        throw new \Exception('BROKEN CODE');

        $saleItem = $item->getSaleItem();

        // TODO
        // - retrieve stock units through order item stock assignments

        // Get subject provider
        $provider = $this->stockUnitResolver->getProviderByRelative($saleItem);
        if (null === $provider) {
            return;
        }

        // Get the stock unit repository
        $subject = $provider->resolve($item->getSaleItem());
        $repository = $provider->getStockUnitRepository();

        // Abort if no subject or no repository
        if (null === $subject || null === $repository) {
            return;
        }

        // Handle opened or pending stock units
        $stockUnits = $repository->findAvailableOrPendingBySubject($subject);
        foreach ($stockUnits as $stockUnit) {
            // Negative quantity case
            if (0 > $quantity) {
                // We can't debit more than the stock unit's current shipped quantity
                if (0 > $stockUnit->getShippedQuantity() + $quantity) {
                    $quantity = $quantity + $stockUnit->getShippedQuantity();
                    $this->stockUnitUpdater->updateShipped($stockUnit, -$stockUnit->getShippedQuantity(), true);
                    if (0 == $quantity) {
                        return;
                    }
                    continue;
                }
                // Else we are done
                $this->stockUnitUpdater->updateShipped($stockUnit, $quantity, true);
                return;
            }

            // Positive quantity case
            // We can't credit more than the stock unit's delivered quantity
            if ($quantity > $stockUnit->getDeliveredQuantity()) {
                $quantity -= $stockUnit->getDeliveredQuantity();
                $this->stockUnitUpdater->updateShipped($stockUnit, $quantity, true);
                continue;
            }
            // Else we are done
            $this->stockUnitUpdater->updateShipped($stockUnit, $quantity, true);
            return;
        }

        // Negative quantity case
        if (0 > $quantity) {
            $stockUnits = $repository->findNewBySubject($subject);
            foreach ($stockUnits as $stockUnit) {
                // We can't debit more than the stock unit's current shipped quantity
                if (0 > $stockUnit->getShippedQuantity() + $quantity) {
                    $quantity = $quantity + $stockUnit->getShippedQuantity();
                    $this->stockUnitUpdater->updateShipped($stockUnit, -$stockUnit->getShippedQuantity(), true);
                    if (0 == $quantity) {
                        return;
                    }
                    continue;
                }
                // Remove the stock unit
                $this->persistenceHelper->remove($stockUnit, true);
                return;
            }

            // Quantity should not be negative here, as we should have managed
            // enough new stock units in the previous loop
            if (0 > $quantity) {
                throw new Exception\RuntimeException("Failed to debit shipped quantity.");
            }
        }

        // Positive quantity case
        // Create a new stock unit for the remaining shipped quantity
        $stockUnit = $repository->createNew();
        $stockUnit
            ->setSubject($subject)
            ->setShippedQuantity($quantity);

        $this->persistenceHelper->persistAndRecompute($stockUnit, true);
    }

    /**
     * Schedules the shipment content change event.
     *
     * @param Model\ShipmentInterface $shipment
     */
    abstract protected function scheduleShipmentContentChangeEvent(Model\ShipmentInterface $shipment);

    /**
     * Returns the shipment item from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return Model\ShipmentItemInterface
     * @throws Exception\InvalidArgumentException
     */
    abstract protected function getShipmentItemFromEvent(ResourceEventInterface $event);
}
