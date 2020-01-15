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
     * @param ShipmentInterface $shipment The shipment
     * @param bool              $force    Whether to force the synchronisation event if the invoice has an id.
     */
    public function synchronize(ShipmentInterface $shipment, bool $force = false): void;
}
