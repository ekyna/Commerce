<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Repository;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface ShipmentPriceRepositoryInterface
 * @package Ekyna\Component\Commerce\Shipment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<ShipmentPriceInterface>
 */
interface ShipmentPriceRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns one price by country, method and weight.
     *
     * @param Decimal $weight The weight in Kg.
     */
    public function findOneByCountryAndMethodAndWeight(
        CountryInterface        $country,
        ShipmentMethodInterface $method,
        Decimal                 $weight
    ): ?ShipmentPriceInterface;

    /**
     * Returns the prices by country and weight.
     *
     * @return array<ShipmentPriceInterface>
     */
    public function findByCountryAndWeight(CountryInterface $country, Decimal $weight, bool $available = true): array;

    /**
     * Returns the prices by country.
     *
     * @return array<ShipmentPriceInterface>
     */
    public function findByCountry(CountryInterface $country): array;
}
