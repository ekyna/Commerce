<?php

namespace Ekyna\Component\Commerce\Invoice\Model;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Resource\Model as Resource;

/**
 * Interface InvoiceInterface
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoiceInterface extends
    DocumentInterface,
    Resource\ResourceInterface,
    Resource\TimestampableInterface,
    Common\NumberSubjectInterface
{
    /**
     * Sets the shipment.
     *
     * @param ShipmentInterface $shipment
     *
     * @return $this|InvoiceInterface
     */
    public function setShipment(ShipmentInterface $shipment = null);

    /**
     * Returns the shipment.
     *
     * @return ShipmentInterface
     */
    public function getShipment();
}
