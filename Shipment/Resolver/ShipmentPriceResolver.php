<?php

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Doctrine\Common\Collections\Criteria;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentMethodRepositoryInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentPriceRepositoryInterface;

/**
 * Class ShipmentPriceResolver
 * @package Ekyna\Component\Commerce\Shipment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPriceResolver
{
    /**
     * @var ShipmentMethodRepositoryInterface
     */
    private $methodRepository;

    /**
     * @var ShipmentPriceRepositoryInterface
     */
    private $priceRepository;


    /**
     * Constructor.
     *
     * @param ShipmentMethodRepositoryInterface $methodRepository
     * @param ShipmentPriceRepositoryInterface  $priceRepository
     */
    public function __construct(
        ShipmentMethodRepositoryInterface $methodRepository,
        ShipmentPriceRepositoryInterface $priceRepository
    ) {
        $this->methodRepository = $methodRepository;
        $this->priceRepository = $priceRepository;
    }

    /**
     * Returns the available shipment methods by country and weight.
     *
     * @param CountryInterface $country
     * @param float            $weight The weight in Kg.
     *
     * @return array|ShipmentMethodInterface[]
     */
    public function getAvailableMethodsByCountryAndWeight(CountryInterface $country, $weight)
    {
        return $this
            ->methodRepository
            ->findAvailableByCountryAndWeight($country, $weight);
    }

    /**
     * Returns the available shipment prices by country and weight.
     *
     * @param CountryInterface $country
     * @param float            $weight The weight in Kg.
     *
     * @return array|ShipmentPriceInterface[]
     */
    public function getAvailablePricesByCountryAndWeight(CountryInterface $country, $weight)
    {
        $results = [];

        $methods = $this->getAvailableMethodsByCountryAndWeight($country, $weight);

        foreach ($methods as $method) {
            /** @var ShipmentPriceInterface[] $prices */
            $prices = $method->getPrices()->matching(
                new Criteria(null, ['weight' => 'ASC'])
            );

            foreach ($prices as $price) {
                if ($weight <= $price->getWeight()) {
                    $results[] = $price;
                    continue 2;
                }
            }
        }

        return $results;
    }

    /**
     * Returns the shipment price by country, method and weight.
     *
     * @param CountryInterface        $country
     * @param ShipmentMethodInterface $method
     * @param float                   $weight The weight in Kg.
     *
     * @return ShipmentPriceInterface
     */
    public function getPriceByCountryAndMethodAndWeight(
        CountryInterface $country,
        ShipmentMethodInterface $method,
        $weight
    ) {
        return $this
            ->priceRepository
            ->findOneByCountryAndMethodAndWeight($country, $method, $weight);
    }
}
