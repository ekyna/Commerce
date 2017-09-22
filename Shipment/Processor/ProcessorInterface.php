<?php

namespace Ekyna\Component\Commerce\Shipment\Processor;

use Psr\Http\Message\ServerRequestInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Interface ProcessorInterface
 * @package Ekyna\Component\Commerce\Shipment\Processor
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProcessorInterface
{
    /**
     * Processes the given shipment.
     *
     * @param ShipmentInterface      $shipment
     * @param ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface|bool
     */
    public function process(ShipmentInterface $shipment, ServerRequestInterface $request);
}
