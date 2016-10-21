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
     * Returns the available shipment methods by sale.
     *
     * @param SaleInterface $sale
     *
     * @return array|ShipmentMethodInterface[]
     */
    public function getAvailableMethodsBySale(SaleInterface $sale);

    /**
     * Returns the available shipment methods by sale.
     *
     * @param SaleInterface $sale
     *
     * @return array|\Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface[]
     */
    public function getAvailablePricesBySale(SaleInterface $sale);

    /**
     * Returns the available shipment methods by country and weight.
     *
     * @param CountryInterface $country
     * @param float            $weight The weight in Kg.
     *
     * @return array|ShipmentMethodInterface[]
     */
    public function getAvailableMethodsByCountryAndWeight(CountryInterface $country, $weight);

    /**
     * Returns the available shipment prices by country and weight.
     *
     * @param CountryInterface $country
     * @param float            $weight The weight in Kg.
     *
     * @return array|\Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface[]
     */
    public function getAvailablePricesByCountryAndWeight(CountryInterface $country, $weight);

    /**
     * Returns the shipment price by country, method and weight.
     *
     * @param CountryInterface        $country
     * @param ShipmentMethodInterface $method
     * @param float                   $weight The weight in Kg.
     *
     * @return \Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface|null
     */
    public function getPriceByCountryAndMethodAndWeight(
        CountryInterface $country,
        ShipmentMethodInterface $method,
        $weight
    );
}
