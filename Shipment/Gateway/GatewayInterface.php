<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Psr\Http\Message\ServerRequestInterface;

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
     * Processes the shipment.
     *
     * @param ShipmentInterface      $shipment
     * @param ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface|bool
     */
    public function process(ShipmentInterface $shipment, ServerRequestInterface $request);

    /**
     * Returns the actions available on a single shipment.
     *
     * @param ShipmentInterface $shipment
     *
     * @return mixed
     */
    public function getActions(ShipmentInterface $shipment);

    /**
     * Returns the mass actions available on shipments.
     *
     * @return mixed
     */
    public function getMassActions();

    /**
     * Returns whether the gateway supports the given shipment.
     *
     * @param ShipmentInterface $shipment
     *
     * @return bool
     */
    public function supports(ShipmentInterface $shipment);
}
