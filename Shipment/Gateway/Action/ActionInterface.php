<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway\Action;

/**
 * Interface RequestInterface
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Request
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ActionInterface
{
    const SCOPE_CHECKOUT = 'scope_checkout';
    const SCOPE_PLATFORM = 'scope_platform';
    const SCOPE_GATEWAY  = 'scope_gateway';

    /**
     * Returns the httpRequest.
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function getHttpRequest();

    /**
     * Returns the shipments.
     *
     * @return \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface[]
     */
    public function getShipments();

    /**
     * Returns the scope of the request.
     *
     * @return string
     */
    static public function getScope();

    /**
     * Returns the name of the request.
     *
     * @return string
     */
    static public function getName();
}
