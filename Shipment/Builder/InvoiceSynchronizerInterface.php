<?php

namespace Ekyna\Component\Commerce\Shipment\Builder;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Interface InvoiceSynchronizerInterface
 * @package Ekyna\Component\Commerce\Shipment\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoiceSynchronizerInterface
{
    /**
     * Synchronize the invoice with its shipment.
     *
     * @param ShipmentInterface $shipment
     */
    public function synchronize(ShipmentInterface $shipment);
}
