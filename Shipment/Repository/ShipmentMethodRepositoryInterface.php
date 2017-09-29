<?php

namespace Ekyna\Component\Commerce\Shipment\Repository;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentZoneInterface;
use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepositoryInterface;

/**
 * Interface ShipmentMethodRepositoryInterface
 * @package Ekyna\Component\Commerce\Shipment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentMethodRepositoryInterface extends TranslatableResourceRepositoryInterface
{
    /**
     * Create a new shipment method with pre-populated messages (one by notifiable state).
     *
     * @return \Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface
     */
    public function createNew();

    /**
     * Returns the shipment methods having shipment prices, optionally filtered by zone.
     *
     * @param ShipmentZoneInterface $zone
     *
     * @return array|\Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface[]
     */
    public function findHavingPrices(ShipmentZoneInterface $zone = null);

    /**
     * Returns the available methods by country and weight.
     *
     * @param CountryInterface $country
     * @param float            $weight
     *
     * @return array|\Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface[]
     */
    public function findAvailableByCountryAndWeight(CountryInterface $country, $weight);
}
