<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Preparer;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

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
     * @return ShipmentInterface|null The prepared shipment.
     */
    public function prepare(SaleInterface $sale): ?ShipmentInterface;

    /**
     * Aborts the sale preparation by canceling the preparation shipment.
     *
     * @return ShipmentInterface|null The canceled shipment.
     */
    public function abort(SaleInterface $sale): ?ShipmentInterface;
}
