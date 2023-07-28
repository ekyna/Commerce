<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway\Virtual;

use Ekyna\Component\Commerce\Shipment\Gateway\AbstractGateway;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayActions;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;

/**
 * Class VirtualGateway
 * @package Ekyna\Component\Commerce\Shipment\Gateway\InStore
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VirtualGateway extends AbstractGateway
{
    public function can(Shipment\ShipmentInterface $shipment, string $action): bool
    {
        if (!($this->supportShipment($shipment, false) && $this->supportAction($action, false))) {
            return false;
        }

        return match ($action) {
            GatewayActions::SHIP   => $shipment->getState() !== Shipment\ShipmentStates::STATE_SHIPPED,
            GatewayActions::CANCEL => $shipment->getState() === Shipment\ShipmentStates::STATE_SHIPPED,
            default                => false,
        };
    }

    public function getCapabilities(): int
    {
        return static::CAPABILITY_SHIPMENT | static::CAPABILITY_VIRTUAL | static::CAPABILITY_SYSTEM;
    }
}
