<?php

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;

/**
 * Interface ShipmentPriceResolverInterface
 * @package Ekyna\Component\Commerce\Shipment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentPriceResolverInterface
{
    /**
     * Returns whether the sale has free shipping.
     *
     * @param SaleInterface           $sale
     * @param ShipmentMethodInterface $method
     *
     * @return bool
     */
    public function hasFreeShipping(SaleInterface $sale, ShipmentMethodInterface $method = null);

    /**
     * Returns the resolved shipment prices by sale.
     *
     * @param SaleInterface $sale
     * @param bool          $availableOnly
     *
     * @return array|\Ekyna\Component\Commerce\Shipment\Model\ResolvedShipmentPrice[]
     */
    public function getAvailablePricesBySale(SaleInterface $sale, $availableOnly = true);

    /**
     * Returns the shipment price by sale.
     *
     * @param SaleInterface $sale
     *
     * @return \Ekyna\Component\Commerce\Shipment\Model\ResolvedShipmentPrice|null
     */
    public function getPriceBySale(SaleInterface $sale);

    /**
     * Returns the shipment price by country, method and weight.
     *
     * @param CountryInterface        $country
     * @param ShipmentMethodInterface $method
     * @param float                   $weight The weight in Kg.
     *
     * @return \Ekyna\Component\Commerce\Shipment\Model\ResolvedShipmentPrice|null
     */
    public function getPriceByCountryAndMethodAndWeight(
        CountryInterface $country,
        ShipmentMethodInterface $method,
        $weight
    );
}
