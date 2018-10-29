<?php

namespace Ekyna\Component\Commerce\Shipment\Repository;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface ShipmentPriceRepositoryInterface
 * @package Ekyna\Component\Commerce\Shipment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentPriceRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns one price by country, method and weight.
     *
     * @param CountryInterface        $country
     * @param ShipmentMethodInterface $method
     * @param float                   $weight The weight in Kg.
     *
     * @return \Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface|null
     */
    public function findOneByCountryAndMethodAndWeight(
        CountryInterface $country,
        ShipmentMethodInterface $method,
        $weight
    );

    /**
     * Returns the prices by country and weight.
     *
     * @param CountryInterface $country
     * @param float            $weight
     * @param bool             $available
     *
     * @return \Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface[]
     */
    public function findByCountryAndWeight(CountryInterface $country, $weight, $available = true);

    /**
     * Returns the prices by country.
     *
     * @param CountryInterface $country
     *
     * @return \Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface[]
     */
    public function findByCountry(CountryInterface $country);
}
