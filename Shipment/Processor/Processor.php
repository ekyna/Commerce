<?php

namespace Ekyna\Component\Commerce\Shipment\Processor;

use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Class Processor
 * @package Ekyna\Component\Commerce\Shipment\Processor
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Processor implements ProcessorInterface
{
    /**
     * @var GatewayRegistryInterface
     */
    private $gatewayRegistry;


    /**
     * Constructor.
     *
     * @param GatewayRegistryInterface $gatewayRegistry
     */
    public function __construct(GatewayRegistryInterface $gatewayRegistry)
    {
        $this->gatewayRegistry = $gatewayRegistry;
    }

    /**
     * @inheritDoc
     */
    public function process(ShipmentInterface $shipment)
    {
        // TODO: Implement process() method.
    }
}
