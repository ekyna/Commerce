<?php

namespace Ekyna\Component\Commerce\Shipment\EventListener;

use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Resource\Event\PersistenceEvent;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class AbstractShipmentListener
 * @package Ekyna\Component\Commerce\Shipment\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractShipmentListener
{
    /**
     * @var NumberGeneratorInterface
     */
    protected $numberGenerator;

    /**
     * @var StateResolverInterface
     */
    protected $stateResolver;


    /**
     * Constructor.
     *
     * @param NumberGeneratorInterface $numberGenerator
     * @param StateResolverInterface   $stateResolver
     */
    public function __construct(
        NumberGeneratorInterface $numberGenerator,
        StateResolverInterface $stateResolver
    ) {
        $this->numberGenerator = $numberGenerator;
        $this->stateResolver = $stateResolver;
    }

    /**
     * Insert event handler.
     *
     * @param PersistenceEvent $event
     */
    public function onInsert(PersistenceEvent $event)
    {
        $shipment = $this->getShipmentFromEvent($event);

        /*
         * TODO this is ugly :s
         * It should be a loop of operations/behaviors ...
         */

        $changed = 0 < count(array_intersect(['items', 'state'], array_keys($event->getChangeSet())));

        // Generate number and key
        $changed = $this->generateNumber($shipment) || $changed;

        // TODO Timestampable behavior/listener
        $shipment
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        if (true || $changed) {
            $event->persistAndRecompute($shipment);
            // Recompute the whole sale
            $event->persistAndRecompute($shipment->getSale());
        }
    }

    /**
     * Update event handler.
     *
     * @param PersistenceEvent $event
     */
    public function onUpdate(PersistenceEvent $event)
    {
        $shipment = $this->getShipmentFromEvent($event);

        // TODO same shit here ... T_T

        $changed = array_key_exists('state', $event->getChangeSet());

        // Generate number and key
        $changed = $this->generateNumber($shipment) || $changed;

        // TODO Timestampable behavior/listener
        $shipment->setUpdatedAt(new \DateTime());

        if (true || $changed) {
            $event->persistAndRecompute($shipment);
            // Recompute the whole sale
            $event->persistAndRecompute($shipment->getSale());
        }
    }

    /**
     * Delete event handler.
     *
     * @param PersistenceEvent $event
     */
    public function onDelete(PersistenceEvent $event)
    {
        $shipment = $this->getShipmentFromEvent($event);

        // Recompute the whole sale
        $event->persistAndRecompute($shipment->getSale());
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
            $this->numberGenerator->generate($shipment);

            return true;
        }

        return false;
    }

    /**
     * Updates the state.
     *
     * @param ShipmentInterface $shipment
     *
     * @return bool Whether the shipment has been changed or not.
     */
    protected function updateState(ShipmentInterface $shipment)
    {
        $state = $this->stateResolver->resolve($shipment);

        if ($state != $shipment->getState()) {
            $shipment->setState($state);

            return true;
        }

        return false;
    }

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
