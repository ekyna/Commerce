<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Shipment\EventListener\ShipmentMethodListener;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistryInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class ShipmentMethodEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodEventSubscriber extends ShipmentMethodListener
{
    public function __construct(
        PersistenceHelperInterface                $persistenceHelper,
        private readonly GatewayRegistryInterface $gatewayRegistry,
    ) {
        parent::__construct($persistenceHelper);
    }

    public function onPreDelete(ResourceEventInterface $event): void
    {
        $this->preventDeletingSystemGateway($event);
    }

    public function onDelete(ResourceEventInterface $event): void
    {
        $this->preventDeletingSystemGateway($event);
    }

    private function preventDeletingSystemGateway(ResourceEventInterface $event): void
    {
        $method = $this->getShipmentMethodFromEvent($event);

        $gateway = $this->gatewayRegistry->getGateway($method->getGatewayName());

        if (!$gateway->supports(GatewayInterface::CAPABILITY_SYSTEM)) {
            return;
        }

        $event->addMessage(
            new ResourceMessage('Can\'t delete system shipment gateway.', ResourceMessage::TYPE_ERROR)
        );
    }
}
