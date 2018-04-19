<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway\Model;
use Ekyna\Component\Commerce\Shipment\Entity\RelayPoint;

/**
 * Class GetRelayPointResponse
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GetRelayPointResponse
{
    /**
     * @var \Ekyna\Component\Commerce\Shipment\Entity\RelayPoint
     */
    private $relayPoint;


    /**
     * Returns the relayPoint.
     *
     * @return \Ekyna\Component\Commerce\Shipment\Entity\RelayPoint
     */
    public function getRelayPoint()
    {
        return $this->relayPoint;
    }

    /**
     * Sets the relayPoint.
     *
     * @param \Ekyna\Component\Commerce\Shipment\Entity\RelayPoint $relayPoint
     *
     * @return GetRelayPointResponse
     */
    public function setRelayPoint(RelayPoint $relayPoint)
    {
        $this->relayPoint = $relayPoint;

        return $this;
    }
}
