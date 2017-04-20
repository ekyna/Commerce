<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Interface PersisterInterface
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PersisterInterface
{
    /**
     * Persists the shipment.
     *
     * @param ShipmentInterface $shipment
     */
    public function persist(ShipmentInterface $shipment): void;

    /**
     * Finalizes the shipment persistence.
     */
    public function flush(): void;
}
