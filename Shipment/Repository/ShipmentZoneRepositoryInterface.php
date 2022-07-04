<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Repository;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentZoneInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface ShipmentZoneRepositoryInterface
 * @package Ekyna\Component\Commerce\Shipment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<ShipmentZoneInterface>
 */
interface ShipmentZoneRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns the shipment zone having shipment prices, optionally filtered by method.
     *
     * @return array<ShipmentZoneInterface>
     */
    public function findHavingPrices(ShipmentMethodInterface $method = null): array;
}
