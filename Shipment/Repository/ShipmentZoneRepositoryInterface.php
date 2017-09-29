<?php

namespace Ekyna\Component\Commerce\Shipment\Repository;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface ShipmentZoneRepositoryInterface
 * @package Ekyna\Component\Commerce\Shipment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentZoneRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns the shipment zone having shipment prices, optionally filtered by method.
     *
     * @param ShipmentMethodInterface $method
     *
     * @return array|\Ekyna\Component\Commerce\Shipment\Model\ShipmentZoneInterface[]
     */
    public function findHavingPrices(ShipmentMethodInterface $method = null);
}
