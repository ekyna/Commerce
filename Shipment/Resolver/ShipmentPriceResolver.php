<?php

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface;
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
     * @var TaxResolverInterface
     */
    private $taxResolver;


    /**
     * Constructor.
     *
     * @param ShipmentPriceRepositoryInterface $priceRepository
     * @param TaxResolverInterface             $taxResolver
     */
    public function __construct(
        ShipmentPriceRepositoryInterface $priceRepository,
        TaxResolverInterface $taxResolver
    ) {
        $this->priceRepository = $priceRepository;
        $this->taxResolver = $taxResolver;
    }

    /**
     * @inheritdoc
     */
    public function hasFreeShipping(SaleInterface $sale)
    {
        if (null !== $customerGroup = $sale->getCustomerGroup()) {
            return $customerGroup->isFreeShipping();
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getAvailablePricesBySale(SaleInterface $sale, $availableOnly = true)
    {
        if (null !== $country = $sale->getDeliveryCountry()) {
            $prices = $this
                ->priceRepository
                ->findByCountryAndWeight($country, $sale->getWeightTotal(), $availableOnly);

            foreach ($prices as $price) {
                $this->addTaxes($price, $country);
            }

            return $prices;
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
        $price = $this
            ->priceRepository
            ->findOneByCountryAndMethodAndWeight($country, $method, $weight);

        if (null !== $price) {
            $this->addTaxes($price, $country);
        }

        return $price;
    }

    /**
     * Add taxes to the shipment prices.
     *
     * @param ShipmentPriceInterface $price
     * @param CountryInterface       $country
     */
    protected function addTaxes(ShipmentPriceInterface $price, CountryInterface $country)
    {
        $price->setTaxes($this->taxResolver->resolveTaxes($price->getMethod(), $country));
    }
}
