<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway\InStore;

use Ekyna\Component\Commerce\Shipment\Gateway\AbstractGateway;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayActions;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;

/**
 * Class InStoreGateway
 * @package Ekyna\Component\Commerce\Shipment\Gateway\InStore
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InStoreGateway extends AbstractGateway
{
    public function can(Shipment\ShipmentInterface $shipment, string $action): bool
    {
        if ($shipment->isReturn()) {
            return parent::can($shipment, $action);
        }

        if (!($this->supportShipment($shipment, false) && $this->supportAction($action, false))) {
            return false;
        }

        switch ($action) {
            case GatewayActions::SHIP:
                return !in_array($shipment->getState(), [
                    Shipment\ShipmentStates::STATE_READY,
                    Shipment\ShipmentStates::STATE_SHIPPED,
                ], true);

            case GatewayActions::CANCEL:
                return $shipment->getState() !== Shipment\ShipmentStates::STATE_CANCELED;

            case GatewayActions::COMPLETE:
                return $shipment->getState() === Shipment\ShipmentStates::STATE_READY;
        }

        return true;
    }

    public function ship(Shipment\ShipmentInterface $shipment): bool
    {
        if ($shipment->isReturn()) {
            return parent::ship($shipment);
        }

        $this->supportShipment($shipment);

        $validStates = [Shipment\ShipmentStates::STATE_READY, Shipment\ShipmentStates::STATE_SHIPPED];

        if (in_array($shipment->getState(), $validStates, true)) {
            return false;
        }

        $shipment->setState(Shipment\ShipmentStates::STATE_READY);

        $this->persister->persist($shipment);

        return true;
    }

    public function complete(Shipment\ShipmentInterface $shipment): bool
    {
        if ($shipment->isReturn()) {
            return parent::complete($shipment);
        }

        $this->supportShipment($shipment);

        if ($shipment->getState() !== Shipment\ShipmentStates::STATE_READY) {
            return false;
        }

        $shipment->setState(Shipment\ShipmentStates::STATE_SHIPPED);

        $this->persister->persist($shipment);

        return true;
    }

    public function getCapabilities(): int
    {
        return static::CAPABILITY_SHIPMENT | static::CAPABILITY_RETURN;
    }
}
