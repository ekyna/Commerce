<?php

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentPriceRepositoryInterface;

/**
 * Class ShipmentPriceResolver
 * @package Ekyna\Component\Commerce\Shipment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPriceResolver implements ShipmentPriceResolverInterface
{
    /**
     * @var ShipmentPriceRepositoryInterface
     */
    private $priceRepository;


    /**
     * Constructor.
     *
     * @param ShipmentPriceRepositoryInterface  $priceRepository
     */
    public function __construct(
        ShipmentPriceRepositoryInterface $priceRepository
    ) {
        $this->priceRepository = $priceRepository;
    }

    /**
     * @inheritdoc
     */
    public function getAvailablePricesBySale(SaleInterface $sale, $availableMethods = true)
    {
        if (null !== $country = $sale->getDeliveryCountry()) {
            return $this->priceRepository->findByCountryAndWeight($country, $sale->getWeightTotal(), $availableMethods);
        }

        return [];
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
