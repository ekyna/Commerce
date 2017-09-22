<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Class AbstractGateway
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractGateway implements GatewayInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config;


    /**
     * Constructor.
     *
     * @param string $name
     * @param array  $config
     */
    public function __construct($name, array $config)
    {
        $this->name = $name;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getActions(ShipmentInterface $shipment)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getMassActions()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function supports(ShipmentInterface $shipment)
    {
        return $shipment->getMethod()->getGatewayName() === $this->getName();
    }
}
