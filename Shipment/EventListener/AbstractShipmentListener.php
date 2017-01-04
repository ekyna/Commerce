<?php

namespace Ekyna\Component\Commerce\Shipment\EventListener;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
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
     * Sets the persistence helper.
     *
     * @param PersistenceHelperInterface $helper
     */
    public function setPersistenceHelper(PersistenceHelperInterface $helper)
    {
        $this->persistenceHelper = $helper;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $shipment = $this->getShipmentFromEvent($event);

        /*
         * TODO this is ugly :s
         * It should be a loop of operations/behaviors ...
         */

        // Generate number and key
        $changed = $this->generateNumber($shipment);

        // TODO Timestampable behavior/listener
        $shipment
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        if (true || $changed) {
            $this->persistenceHelper->persistAndRecompute($shipment);
        }

        $sale = $shipment->getSale();
        $sale->addShipment($shipment);

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

        // TODO same shit here ... T_T
        $doScheduleSaleContentChange = false;

        // Generate number and key
        $changed = $this->generateNumber($shipment);

        if ($this->persistenceHelper->isChanged($shipment, 'state')) {
            // Handle "Completed at" datetime
            if (($shipment->getState() === ShipmentStates::STATE_COMPLETED) && (null === $shipment->getCompletedAt())) {
                $shipment->setCompletedAt(new \DateTime());
                $changed = true;
            } elseif (($shipment->getState() !== ShipmentStates::STATE_COMPLETED) && (null !== $shipment->getCompletedAt())) {
                $shipment->setCompletedAt(null);
                $changed = true;
            }

            $doScheduleSaleContentChange = true;
            $this->scheduleSaleContentChangeEvent($shipment->getSale());
        }

        // TODO Timestampable behavior/listener
        $shipment->setUpdatedAt(new \DateTime());

        if (true || $changed) {
            $this->persistenceHelper->persistAndRecompute($shipment);
        }

        if ($doScheduleSaleContentChange) {
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
     * Pre update event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onPreUpdate(ResourceEventInterface $event)
    {
        /*$shipment = $this->getShipmentFromEvent($event);
        // TODO assert updateable states
        if (!in_array($shipment->getState(), ShipmentStates::getDeletableStates())) {
            throw new IllegalOperationException();
        }*/
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
        $shipment = $this->getShipmentFromEvent($event);

        // TODO look for returns ?

        if (!in_array($shipment->getState(), ShipmentStates::getDeletableStates())) {
            throw new IllegalOperationException();
        }
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
            if (null === $sale = $shipment->getSale()) {
                return false;
            }

            $number = 1;
            foreach ($sale->getShipments() as $s) {
                if (preg_match('~\d+-(\d+)~', $s->getNumber(), $matches)) {
                    $n = intval($matches[1]);
                    if ($number <= $n) {
                        $number = $n + 1;
                    }
                }
            }

            $shipment->setNumber(sprintf('%s-%s', $sale->getNumber(), $number));

            return true;
        }

        return false;
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
