<?php

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
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
     * @var ContextProviderInterface
     */
    protected $contextProvider;


    /**
     * Constructor.
     *
     * @param ShipmentPriceRepositoryInterface $priceRepository
     * @param ShipmentRuleRepositoryInterface  $ruleRepository
     * @param TaxResolverInterface             $taxResolver
     * @param ContextProviderInterface         $contextProvider
     */
    public function __construct(
        ShipmentPriceRepositoryInterface $priceRepository,
        ShipmentRuleRepositoryInterface $ruleRepository,
        TaxResolverInterface $taxResolver,
        ContextProviderInterface $contextProvider
    ) {
        $this->priceRepository = $priceRepository;
        $this->ruleRepository = $ruleRepository;
        $this->taxResolver = $taxResolver;
        $this->contextProvider = $contextProvider;
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
        $context = $this->contextProvider->getContext($sale);

        $prices = $this
            ->priceRepository
            ->findByCountryAndWeight($context->getDeliveryCountry(), $sale->getWeightTotal(), $availableOnly);

        foreach ($prices as $price) {
            $this->addTaxes($price, $context->getDeliveryCountry());
            $price->setFree($this->hasFreeShipping($sale, $price->getMethod()));
        }

        usort($prices, function (ShipmentPriceInterface $a, ShipmentPriceInterface $b) {
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

    /**
     * @inheritdoc
     */
    public function getPriceBySale(SaleInterface $sale)
    {
        if (null === $method = $sale->getShipmentMethod()) {
            throw new RuntimeException("Sale's shipment method must be set.");
        }

        $context = $this->contextProvider->getContext($sale);

        $price = $this
            ->priceRepository
            ->findOneByCountryAndMethodAndWeight($context->getDeliveryCountry(), $method, $sale->getWeightTotal());

        if ($price) {
            $this->addTaxes($price, $context->getDeliveryCountry());
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
