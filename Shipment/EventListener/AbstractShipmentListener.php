<?php

namespace Ekyna\Component\Commerce\Shipment\EventListener;

use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Shipment\Builder\InvoiceSynchronizerInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\WeightCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;
use Ekyna\Component\Commerce\Stock\Assigner\StockUnitAssignerInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractShipmentListener
 * @package Ekyna\Component\Commerce\Shipment\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractShipmentListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var NumberGeneratorInterface
     */
    protected $numberGenerator;

    /**
     * @var WeightCalculatorInterface
     */
    protected $weightCalculator;

    /**
     * @var StockUnitAssignerInterface
     */
    protected $stockUnitAssigner;

    /**
     * @var InvoiceSynchronizerInterface
     */
    protected $invoiceSynchronizer;


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
     * Sets the number generator.
     *
     * @param NumberGeneratorInterface $generator
     */
    public function setNumberGenerator(NumberGeneratorInterface $generator)
    {
        $this->numberGenerator = $generator;
    }

    /**
     * Sets the weight calculator.
     *
     * @param WeightCalculatorInterface $calculator
     */
    public function setWeightCalculator(WeightCalculatorInterface $calculator)
    {
        $this->weightCalculator = $calculator;
    }

    /**
     * Sets the stock assigner.
     *
     * @param StockUnitAssignerInterface $assigner
     */
    public function setStockUnitAssigner(StockUnitAssignerInterface $assigner)
    {
        $this->stockUnitAssigner = $assigner;
    }

    /**
     * Sets the invoice synchronizer.
     *
     * @param InvoiceSynchronizerInterface $synchronizer
     */
    public function setInvoiceSynchronizer(InvoiceSynchronizerInterface $synchronizer)
    {
        $this->invoiceSynchronizer = $synchronizer;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $shipment = $this->getShipmentFromEvent($event);

        // Generate number and key
        $changed = $this->generateNumber($shipment);

        // Completed state
        $changed |= $this->handleCompletedState($shipment);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($shipment);
        }

        $sale = $this->getSaleFromShipment($shipment);
        $sale->addShipment($shipment); // TODO wtf ?

        $this->invoiceSynchronizer->synchronize($shipment);

        $this->scheduleSaleContentChangeEvent($sale);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $shipment = $this->getShipmentFromEvent($event);

        $this->preventForbiddenChange($shipment);

        // Generate number and key
        $changed = $this->generateNumber($shipment);

        $stateChanged = $this->persistenceHelper->isChanged($shipment, 'state');
        if ($stateChanged) {
            $changed |= $this->handleCompletedState($shipment);
        }

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($shipment);
        }

        if ($stateChanged) {
            $stateCs = $this->persistenceHelper->getChangeSet($shipment, 'state');

            // If shipment state has changed from non stockable to stockable
            if (ShipmentStates::hasChangedToStockable($stateCs)) {
                // For each shipment item
                foreach ($shipment->getItems() as $item) {
                    // If not scheduled for insert
                    if (!$this->persistenceHelper->isScheduledForInsert($item)) {
                        // Credit sale item stock units shipped quantity through assignments
                        $this->stockUnitAssigner->assignShipmentItem($item);
                    }
                }
            }
            // Else if shipment state has changed from stockable to non stockable
            elseif (ShipmentStates::hasChangedFromStockable($stateCs)) {
                // For each shipment item
                foreach ($shipment->getItems() as $item) {
                    // If not scheduled for remove
                    if (!$this->persistenceHelper->isScheduledForRemove($item)) {
                        // Debit sale item stock units shipped quantity through assignments
                        $this->stockUnitAssigner->detachShipmentItem($item);
                    }
                }
            }
        }

        $this->invoiceSynchronizer->synchronize($shipment);

        if ($changed || $stateChanged) {
            $this->scheduleSaleContentChangeEvent($this->getSaleFromShipment($shipment));
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $shipment = $this->getShipmentFromEvent($event);

        $this->invoiceSynchronizer->synchronize($shipment);

        $sale = $this->getSaleFromShipment($shipment);

        $sale->removeShipment($shipment);

        $this->scheduleSaleContentChangeEvent($sale);
    }

    /**
     * Content change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onContentChange(ResourceEventInterface $event)
    {
        $shipment = $this->getShipmentFromEvent($event);

        $this->invoiceSynchronizer->synchronize($shipment);

        $this->scheduleSaleContentChangeEvent($this->getSaleFromShipment($shipment));
    }

    /**
     * Pre create event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreCreate(ResourceEventInterface $event)
    {
        $shipment = $this->getShipmentFromEvent($event);

        $this->preLoadSale($shipment->getSale());
    }

    /**
     * Pre update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreUpdate(ResourceEventInterface $event)
    {
        $shipment = $this->getShipmentFromEvent($event);

        $this->preLoadSale($shipment->getSale());
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        $shipment = $this->getShipmentFromEvent($event);

        $this->preLoadSale($shipment->getSale());
    }

    /**
     * Pre loads the sale's shipments and invoices.
     *
     * @param SaleInterface $sale
     */
    private function preLoadSale(SaleInterface $sale)
    {
        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface $sale */
        $sale->getShipments()->toArray();
        /** @var \Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface $sale */
        $sale->getInvoices()->toArray();
    }

    /**
     * Generates the number.
     *
     * @param ShipmentInterface $shipment
     *
     * @return bool Whether the shipment has been generated or not.
     */
    protected function generateNumber(ShipmentInterface $shipment)
    {
        if (0 == strlen($shipment->getNumber())) {
            $this->numberGenerator->generate($shipment);

            return true;
        }

        return false;
    }

    /**
     * Handle the 'completed' state.
     *
     * @param ShipmentInterface $shipment
     *
     * @return bool Whether or not the shipment has been changed.
     */
    protected function handleCompletedState(ShipmentInterface $shipment)
    {
        $changed = false;

        $state = $shipment->getState();
        $shippedAt = $shipment->getShippedAt();
        $completedAt = $shipment->getCompletedAt();

        if (in_array($state, [ShipmentStates::STATE_SHIPPED, ShipmentStates::STATE_COMPLETED], true)) {
            if (null === $shippedAt) {
                $shipment->setShippedAt(new \DateTime());
                $changed = true;
            }
            if ($state === ShipmentStates::STATE_COMPLETED && null === $completedAt) {
                $shipment->setCompletedAt(new \DateTime());
                $changed = true;
            } elseif (null !== $completedAt) {
                $shipment->setCompletedAt(null);
                $changed = true;
            }
        } else {
            if (null !== $shippedAt) {
                $shipment->setShippedAt(null);
                $changed = true;
            }
            if (null !== $completedAt) {
                $shipment->setCompletedAt(null);
                $changed = true;
            }
        }

        return $changed;
    }

    /**
     * Prevents some of the shipment's fields to change.
     *
     * @param ShipmentInterface $shipment
     */
    protected function preventForbiddenChange(ShipmentInterface $shipment)
    {
        if ($this->persistenceHelper->isChanged($shipment, 'return')) {
            list($old, $new) = $this->persistenceHelper->getChangeSet($shipment, 'return');
            if ($old != $new) {
                throw new RuntimeException("Changing the shipment type is not yet supported.");
            }
        }
    }

    /**
     * Returns the shipment's sale.
     *
     * @param ShipmentInterface $shipment
     *
     * @return SaleInterface|ShipmentSubjectInterface
     */
    protected function getSaleFromShipment(ShipmentInterface $shipment)
    {
        if (null === $sale = $shipment->getSale()) {
            $cs = $this->persistenceHelper->getChangeSet($shipment, $this->getSalePropertyPath());
            if (!empty($cs)) {
                $sale = $cs[0];
            }
        }

        if (!$sale instanceof SaleInterface) {
            throw new RuntimeException("Failed to retrieve shipment's sale.");
        }

        return $sale;
    }

    /**
     * Dispatches the sale content change event.
     *
     * @param SaleInterface $sale
     */
    abstract protected function scheduleSaleContentChangeEvent(SaleInterface $sale);

    /**
     * Returns the shipment from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return ShipmentInterface
     * @throws InvalidArgumentException
     */
    abstract protected function getShipmentFromEvent(ResourceEventInterface $event);

    /**
     * Returns the shipment's sale property path.
     *
     * @return string
     */
    abstract protected function getSalePropertyPath();
}
