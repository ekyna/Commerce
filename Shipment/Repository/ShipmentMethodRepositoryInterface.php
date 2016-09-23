<?php

namespace Ekyna\Component\Commerce\Shipment\Repository;

/**
 * Interface ShipmentMethodRepositoryInterface
 * @package Ekyna\Component\Commerce\Shipment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentMethodRepositoryInterface
{
    /**
     * Create a new shipment method with pre-populated messages (one by notifiable state).
     *
     * @return \Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface
     */
    public function createNew();
}
