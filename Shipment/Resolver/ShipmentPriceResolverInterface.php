<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Shipment\Model\ResolvedShipmentPrice;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;

/**
 * Interface ShipmentPriceResolverInterface
 * @package Ekyna\Component\Commerce\Shipment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentPriceResolverInterface
{
    /**
     * Returns the resolved shipment prices by sale.
     *
     * @return array<ResolvedShipmentPrice>
     */
    public function getAvailablePricesBySale(SaleInterface $sale, bool $availableOnly = true): array;

    /**
     * Returns the shipment price by sale.
     */
    public function getPriceBySale(SaleInterface $sale): ?ResolvedShipmentPrice;

    /**
     * Returns the shipment price WITHOUT TAXES by country, method and weight.
     *
     * @param Decimal $weight The weight in Kg.
     */
    public function getPriceByCountryAndMethodAndWeight(
        CountryInterface        $country,
        ShipmentMethodInterface $method,
        Decimal                 $weight
    ): ?ResolvedShipmentPrice;
}
