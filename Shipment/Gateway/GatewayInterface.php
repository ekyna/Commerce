<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Shipment\Gateway\Action\ActionInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Interface GatewayInterface
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface GatewayInterface
{
    /**
     * Returns the gateway name.
     *
     * @return string
     */
    public function getName();

    /**
     * Executes the given action.
     *
     * @param ActionInterface $action
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    public function execute(ActionInterface $action);

    /**
     * Returns whether the given action is supported.
     *
     * @param ActionInterface $action
     *
     * @return bool
     */
    public function supports(ActionInterface $action);

    /**
     * Returns the FQCN of the supported actions (optionally filtered regarding to the given shipment).
     *
     * @param ShipmentInterface $shipment
     *
     * @return array
     */
    public function getActions(ShipmentInterface $shipment = null);
}
