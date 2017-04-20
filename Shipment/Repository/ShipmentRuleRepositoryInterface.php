<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Repository;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentRuleInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface ShipmentRuleRepositoryInterface
 * @package Ekyna\Component\Commerce\Shipment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentRuleRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the shipment rule for the given sale and (optionally) method.
     *
     * @param SaleInterface                $sale
     * @param ShipmentMethodInterface|null $method
     *
     * @return ShipmentRuleInterface|null
     */
    public function findOneBySale(SaleInterface $sale, ShipmentMethodInterface $method = null): ?ShipmentRuleInterface;
}
