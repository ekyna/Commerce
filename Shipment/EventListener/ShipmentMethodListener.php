<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\EventListener;

use Behat\Transliterator\Transliterator;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

use function sprintf;
use function uniqid;

/**
 * Class ShipmentMethodListener
 * @package Ekyna\Component\Commerce\Shipment\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodListener
{
    public function __construct(
        private readonly PersistenceHelperInterface $persistenceHelper
    ) {
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event): void
    {
        $method = $this->getShipmentMethodFromEvent($event);

        if (!empty($method->getGatewayName())) {
            return;
        }

        $method->setGatewayName(sprintf(
            '%s-%s',
            Transliterator::transliterate($method->getPlatformName()),
            uniqid()
        ));

        // TODO check uniqueness

        $this->persistenceHelper->persistAndRecompute($method, false);
    }

    /**
     * Returns the shipment method from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return ShipmentMethodInterface
     * @throws UnexpectedTypeException
     */
    protected function getShipmentMethodFromEvent(ResourceEventInterface $event): ShipmentMethodInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof ShipmentMethodInterface) {
            throw new UnexpectedTypeException($resource, ShipmentMethodInterface::class);
        }

        return $resource;
    }
}
