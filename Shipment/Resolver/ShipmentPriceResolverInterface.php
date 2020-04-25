<?php

namespace Ekyna\Component\Commerce\Shipment\Resolver;

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
     * @param SaleInterface $sale
     * @param bool          $availableOnly
     *
     * @return array|ResolvedShipmentPrice[]
     */
    public function getAvailablePricesBySale(SaleInterface $sale, bool $availableOnly = true): array;

    /**
     * Returns the shipment price by sale.
     *
     * @param SaleInterface $sale
     *
     * @return ResolvedShipmentPrice|null
     */
    public function getPriceBySale(SaleInterface $sale): ?ResolvedShipmentPrice;

    /**
     * Returns the shipment price WITHOUT TAXES by country, method and weight.
     *
     * @param CountryInterface        $country
     * @param ShipmentMethodInterface $method
     * @param float                   $weight The weight in Kg.
     *
     * @return ResolvedShipmentPrice|null
     */
    public function getPriceByCountryAndMethodAndWeight(
        CountryInterface $country,
        ShipmentMethodInterface $method,
        float $weight
    ): ?ResolvedShipmentPrice;
}
