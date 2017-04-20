<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Builder;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Interface ShipmentBuilderInterface
 * @package Ekyna\Component\Commerce\Shipment\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentBuilderInterface
{
    /**
     * Builds the shipment.
     */
    public function build(ShipmentInterface $shipment): void;
}
