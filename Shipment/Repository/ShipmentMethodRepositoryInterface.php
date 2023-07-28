<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Repository;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentZoneInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;
use Ekyna\Component\Resource\Repository\TranslatableRepositoryInterface;

/**
 * Interface ShipmentMethodRepositoryInterface
 * @package Ekyna\Component\Commerce\Shipment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<ShipmentMethodRepositoryInterface>
 */
interface ShipmentMethodRepositoryInterface extends TranslatableRepositoryInterface
{
    /**
     * Returns the shipment methods having shipment prices, optionally filtered by zone.
     *
     * @return array<ShipmentMethodInterface>
     */
    public function findHavingPrices(ShipmentZoneInterface $zone = null): array;

    /**
     * Returns the available methods by country and weight.
     *
     * @return array<ShipmentMethodInterface>
     */
    public function findAvailableByCountryAndWeight(CountryInterface $country, Decimal $weight): array;

    /**
     * Finds the enabled shipment method by platform name.
     *
     * @param string $platformName
     *
     * @return ShipmentMethodInterface|null
     */
    public function findOneByPlatform(string $platformName): ?ShipmentMethodInterface;
}
