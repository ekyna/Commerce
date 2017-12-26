<?php

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Doctrine\Common\Collections\Criteria;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentMethodRepositoryInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentPriceRepositoryInterface;

/**
 * Class ShipmentPriceResolver
 * @package Ekyna\Component\Commerce\Shipment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPriceResolver implements ShipmentPriceResolverInterface
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
     * @inheritdoc
     */
    public function getAvailableMethodsBySale(SaleInterface $sale)
    {
        if (null !== $country = $sale->getDeliveryCountry()) {
            return $this->getAvailableMethodsByCountryAndWeight($country, $sale->getWeightTotal());
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAvailablePricesBySale(SaleInterface $sale)
    {
        if (null !== $country = $sale->getDeliveryCountry()) {
            return $this->getAvailablePricesByCountryAndWeight($country, $sale->getWeightTotal());
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAvailableMethodsByCountryAndWeight(CountryInterface $country, $weight)
    {
        return $this
            ->methodRepository
            ->findAvailableByCountryAndWeight($country, $weight);
    }

    /**
     * @inheritdoc
     */
    public function getAvailablePricesByCountryAndWeight(CountryInterface $country, $weight)
    {
        $results = [];

        $methods = $this->getAvailableMethodsByCountryAndWeight($country, $weight);

        foreach ($methods as $method) {
            /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface[] $prices */
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
     * @inheritdoc
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
