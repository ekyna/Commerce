<?php

namespace Ekyna\Component\Commerce\Shipment\EventListener;

use Behat\Transliterator\Transliterator;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class ShipmentMethodListener
 * @package Ekyna\Component\Commerce\Shipment\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper)
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
        $method = $this->getShipmentMethodFromEvent($event);

        if (empty($method->getGatewayName())) {
            $method->setGatewayName(sprintf(
                '%s-%s',
                Transliterator::transliterate($method->getPlatformName()),
                uniqid()
            ));

            // TODO check uniqueness

            $this->persistenceHelper->persistAndRecompute($method);
        }
    }

    /**
     * Returns the shipment method from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return ShipmentMethodInterface
     * @throws InvalidArgumentException
     */
    private function getShipmentMethodFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof ShipmentMethodInterface) {
            throw new InvalidArgumentException('Expected instance of ' . ShipmentMethodInterface::class);
        }

        return $resource;
    }
}
