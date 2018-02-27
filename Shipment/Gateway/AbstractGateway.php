<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Shipment\Gateway\Action\ActionInterface;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Class AbstractGateway
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractGateway implements GatewayInterface, Shipment\WeightCalculatorAwareInterface
{
    use Shipment\WeightCalculatorAwareTrait;

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
    public function execute(ActionInterface $action)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function supports(ActionInterface $action)
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getActions(Shipment\ShipmentInterface $shipment = null)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getTrackingUrl(ShipmentInterface $shipment)
    {
        return null;
    }
}
