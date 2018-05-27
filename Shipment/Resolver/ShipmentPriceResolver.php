<?php

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentPriceRepositoryInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentRuleRepositoryInterface;

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
     * @var ShipmentRuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var TaxResolverInterface
     */
    private $taxResolver;


    /**
     * Constructor.
     *
     * @param ShipmentPriceRepositoryInterface $priceRepository
     * @param ShipmentRuleRepositoryInterface  $ruleRepository
     * @param TaxResolverInterface             $taxResolver
     */
    public function __construct(
        ShipmentPriceRepositoryInterface $priceRepository,
        ShipmentRuleRepositoryInterface $ruleRepository,
        TaxResolverInterface $taxResolver
    ) {
        $this->priceRepository = $priceRepository;
        $this->ruleRepository = $ruleRepository;
        $this->taxResolver = $taxResolver;
    }

    /**
     * @inheritdoc
     */
    public function hasFreeShipping(SaleInterface $sale, ShipmentMethodInterface $method = null)
    {
        return null !== $this->ruleRepository->findOneBySale($sale, $method);
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
                $price->setFree($this->hasFreeShipping($sale, $price->getMethod()));
            }

            usort($prices, function(ShipmentPriceInterface $a, ShipmentPriceInterface $b) {
                $aFree = $a->isFree() || 0 === $a->getNetPrice();
                $bFree = $b->isFree() || 0 === $b->getNetPrice();

                if ($aFree && !$bFree) {
                    return -1;
                }
                if (!$aFree && $bFree) {
                    return 1;
                }

                return $a->getNetPrice() >= $b->getNetPrice() ? 1 : -1;
            });

            return $prices;
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function getPriceBySale(SaleInterface $sale)
    {
        if (null === $country = $sale->getDeliveryCountry()) {
            throw new RuntimeException("Sale's delivery address country must be set.");
        }
        if (null === $method = $sale->getShipmentMethod()) {
            throw new RuntimeException("Sale's shipment method must be set.");
        }

        $price = $this
            ->priceRepository
            ->findOneByCountryAndMethodAndWeight($country, $method, $sale->getWeightTotal());

        if ($price) {
            $this->addTaxes($price, $country);
            $price->setFree($this->hasFreeShipping($sale, $price->getMethod()));
        }

        return $price;
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
