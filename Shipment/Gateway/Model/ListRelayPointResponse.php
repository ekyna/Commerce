<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway\Model;

use Ekyna\Component\Commerce\Shipment\Entity\RelayPoint;

/**
 * Class ListRelayPointResponse
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ListRelayPointResponse
{
    /** @var array<RelayPoint> */
    private array $relayPoints = [];

    public function addRelayPoint(RelayPoint $relayPoint): ListRelayPointResponse
    {
        $this->relayPoints[] = $relayPoint;

        return $this;
    }

    /**
     * @return array<RelayPoint>
     */
    public function getRelayPoints(): array
    {
        return $this->relayPoints;
    }
}
