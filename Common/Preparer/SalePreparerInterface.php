<?php

namespace Ekyna\Component\Commerce\Common\Preparer;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;

/**
 * Interface SalePreparerInterface
 * @package Ekyna\Component\Commerce\Common\Preparer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SalePreparerInterface
{
    /**
     * Prepares the sale by prioritizing the sale and building shipment at preparation state.
     *
     * @param SaleInterface $sale
     *
     * @return \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface The prepared shipment.
     */
    public function prepare(SaleInterface $sale);

    /**
     * Aborts the sale preparation by canceling the preparation shipment.
     *
     * @param SaleInterface $sale
     *
     * @return \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface The canceled shipment.
     */
    public function abort(SaleInterface $sale);
}
