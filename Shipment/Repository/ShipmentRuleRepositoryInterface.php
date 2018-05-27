<?php

namespace Ekyna\Component\Commerce\Shipment\Repository;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;

/**
 * Interface ShipmentRuleRepositoryInterface
 * @package Ekyna\Component\Commerce\Shipment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentRuleRepositoryInterface
{
    /**
     * Finds the shipment rule for the given sale and (optionally) method.
     *
     * @param SaleInterface                $sale
     * @param ShipmentMethodInterface|null $method
     *
     * @return \Ekyna\Component\Commerce\Shipment\Model\ShipmentRuleInterface|null
     */
    public function findOneBySale(SaleInterface $sale, ShipmentMethodInterface $method = null);
}