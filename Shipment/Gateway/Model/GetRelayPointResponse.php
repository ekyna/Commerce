<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway\Model;
use Ekyna\Component\Commerce\Shipment\Entity\RelayPoint;

/**
 * Class GetRelayPointResponse
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GetRelayPointResponse
{
    private ?RelayPoint $relayPoint = null;

    public function getRelayPoint(): ?RelayPoint
    {
        return $this->relayPoint;
    }

    public function setRelayPoint(?RelayPoint $relayPoint): GetRelayPointResponse
    {
        $this->relayPoint = $relayPoint;

        return $this;
    }
}
