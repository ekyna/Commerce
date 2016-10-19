<?php

namespace Ekyna\Component\Commerce\Shipment\Repository;

use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepositoryInterface;

/**
 * Interface ShipmentMethodRepositoryInterface
 * @package Ekyna\Component\Commerce\Shipment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentMethodRepositoryInterface extends TranslatableResourceRepositoryInterface
{
    /**
     * Create a new shipment method with pre-populated messages (one by notifiable state).
     *
     * @return \Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface
     */
    public function createNew();
}
