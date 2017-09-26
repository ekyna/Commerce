<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway\Action;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AbstractAction
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAction implements ActionInterface
{
    /**
     * @var ServerRequestInterface
     */
    private $httpRequest;

    /**
     * @var ShipmentInterface[]
     */
    private $shipments;


    /**
     * Constructor.
     *
     * @param ServerRequestInterface $httpRequest
     * @param ShipmentInterface[]    $shipments
     */
    public function __construct(ServerRequestInterface $httpRequest, array $shipments = [])
    {
        foreach ($shipments as $shipment) {
            if (!$shipment instanceof ShipmentInterface) {
                throw new InvalidArgumentException("Expected instance of " . ShipmentInterface::class);
            }
        }

        $this->httpRequest = $httpRequest;
        $this->shipments = $shipments;
    }

    /**
     * @inheritDoc
     */
    public function getHttpRequest()
    {
        return $this->httpRequest;
    }

    /**
     * @inheritDoc
     */
    public function getShipments()
    {
        return $this->shipments;
    }

    /**
     * @inheritDoc
     */
    abstract static public function getScopes();
}
