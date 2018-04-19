<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway\Model;

use Ekyna\Component\Commerce\Shipment\Entity\RelayPoint;

/**
 * Class ListRelayPointResponse
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ListRelayPointResponse
{
    /**
     * @var RelayPoint[]
     */
    private $relayPoints = [];


    /**
     * Adds the relay point.
     *
     * @param RelayPoint $relayPoint
     *
     * @return ListRelayPointResponse
     */
    public function addRelayPoint(RelayPoint $relayPoint)
    {
        $this->relayPoints[] = $relayPoint;

        return $this;
    }

    /**
     * Returns the relay points.
     *
     * @return \Ekyna\Component\Commerce\Shipment\Entity\RelayPoint[]
     */
    public function getRelayPoints()
    {
        return $this->relayPoints;
    }
}
