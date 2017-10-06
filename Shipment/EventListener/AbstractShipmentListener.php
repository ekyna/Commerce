<?php

namespace Ekyna\Component\Commerce\Shipment\EventListener;

use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Shipment\Calculator\WeightCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
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
     * @param NumberGeneratorInterface $numberGenerator
     */
    public function setNumberGenerator(NumberGeneratorInterface $numberGenerator)
    {
        $this->numberGenerator = $numberGenerator;
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
     * @param StockUnitAssignerInterface $stockUnitAssigner
     */
    public function setStockUnitAssigner(StockUnitAssignerInterface $stockUnitAssigner)
    {
        $this->stockUnitAssigner = $stockUnitAssigner;
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

        // Total weight
        $changed |= $this->calculateWeight($shipment);

        // Completed state
        $changed |= $this->handleCompletedState($shipment);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($shipment);
        }

        $sale = $shipment->getSale();
        $sale->addShipment($shipment); // TODO wtf ?

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

        $dispatchSaleContentChange = false;

        // Generate number and key
        $changed = $this->generateNumber($shipment);

        // Total weight
        $changed |= $this->calculateWeight($shipment);

        if ($this->persistenceHelper->isChanged($shipment, 'state')) {
            $dispatchSaleContentChange = true;
            $changed |= $this->handleCompletedState($shipment);
        }

        if ($changed) {
            $dispatchSaleContentChange = true;
            $this->persistenceHelper->persistAndRecompute($shipment);
        }

        if ($this->persistenceHelper->isChanged($shipment, 'state')) {
            $stateCs = $this->persistenceHelper->getChangeSet($shipment, 'state');
            // If shipment state has changed from non stockable to stockable
            if (ShipmentStates::hasChangedToStockable($stateCs)) {
                // For each shipment item
                /*if ($shipment->isReturn()) {
                    foreach ($shipment->getItems() as $item) {
                        // Credit sale item stock units shipped quantity through assignments
                        $this->stockUnitAssigner->detachShipmentItem($item);
                    }
                } else {*/
                    foreach ($shipment->getItems() as $item) {
                        // Credit sale item stock units shipped quantity through assignments
                        $this->stockUnitAssigner->assignShipmentItem($item);
                    }
                //}
            } // Else if shipment state has changed from stockable to non stockable
            elseif (ShipmentStates::hasChangedFromStockable($stateCs)) {
                // For each shipment item
                /*if ($shipment->isReturn()) {
                    foreach ($shipment->getItems() as $item) {
                        // Debit sale item stock units shipped quantity through assignments
                        $this->stockUnitAssigner->assignShipmentItem($item);
                    }
                } else {*/
                    foreach ($shipment->getItems() as $item) {
                        // Debit sale item stock units shipped quantity through assignments
                        $this->stockUnitAssigner->detachShipmentItem($item);
                    }
                //}
            }
        }

        if ($dispatchSaleContentChange) {
            $this->scheduleSaleContentChangeEvent($shipment->getSale());
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

        $this->scheduleSaleContentChangeEvent($shipment->getSale());
    }

    /**
     * Content change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onContentChange(ResourceEventInterface $event)
    {
        $shipment = $this->getShipmentFromEvent($event);

        $this->scheduleSaleContentChangeEvent($shipment->getSale());
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
     * Calculates the weight.
     *
     * @param ShipmentInterface $shipment
     *
     * @return bool Whether the shipment has been generated or not.
     */
    protected function calculateWeight(ShipmentInterface $shipment)
    {
        if (0 < $shipment->getWeight()) {
            return false;
        }

        $weight = $this->weightCalculator->calculateShipment($shipment);

        if ($weight !== $shipment->getWeight()) {
            $shipment->setWeight($weight);

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
        $completedAt = $shipment->getCompletedAt();

        if ($state === ShipmentStates::STATE_COMPLETED && null === $completedAt) {
            $shipment->setCompletedAt(new \DateTime());
            $changed = true;
        } elseif ($state != ShipmentStates::STATE_COMPLETED && null !== $completedAt) {
            $shipment->setCompletedAt(null);
            $changed = true;
        }

        return $changed;
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
}
