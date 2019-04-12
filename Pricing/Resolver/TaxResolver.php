<?php

namespace Ekyna\Component\Commerce\Pricing\Resolver;

use Ekyna\Component\Commerce\Common\Country\CountryProviderInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;
use Ekyna\Component\Commerce\Pricing\Model\VatNumberSubjectInterface;
use Ekyna\Component\Commerce\Pricing\Repository\TaxRuleRepositoryInterface;

/**
 * Class TaxResolver
 * @package Ekyna\Component\Commerce\Pricing\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxResolver implements TaxResolverInterface
{
    /**
     * @var CountryProviderInterface
     */
    protected $countryProvider;

    /**
     * @var TaxRuleRepositoryInterface
     */
    protected $taxRuleRepository;

    /**
     * @var array
     */
    protected $cache;


    /**
     * Constructor.
     *
     * @param CountryProviderInterface   $countryProvider
     * @param TaxRuleRepositoryInterface $taxRuleRepository
     */
    public function __construct(
        CountryProviderInterface $countryProvider,
        TaxRuleRepositoryInterface $taxRuleRepository
    ) {
        $this->countryProvider = $countryProvider;
        $this->taxRuleRepository = $taxRuleRepository;

        $this->cache = new ResolvedTaxesCache();
    }

    /**
     * @inheritdoc
     *
     * @see https://ec.europa.eu/taxation_customs/business/vat/eu-vat-rules-topic/where-tax_fr
     */
    public function resolveTaxes(TaxableInterface $taxable, $target = null): array
    {
        // TODO @param ContextInterface $context (instead of $target)

        // Abort if taxable does not have tax group
        if (null === $taxGroup = $taxable->getTaxGroup()) {
            return [];
        }

        // Abort if tax group does not have taxes
        if (!$taxGroup->hasTaxes()) {
            return [];
        }

        $country = $this->resolveTargetCountry($target);
        $business = $target instanceof VatNumberSubjectInterface
            ? $target->isBusiness()
            : false;

        // Use cached resolved taxes if any
        if (null !== $taxes = $this->cache->get($taxGroup, $country, $business)) {
            return $taxes;
        }

        // Abort if no matching tax rule
        if (null === $taxRule = $this->resolveTaxRule($country, $business)) {
            return [];
        }

        // Abort if tax rule does not have taxes
        if (!$taxRule->hasTaxes()) {
            return [];
        }

        // Resolves the taxes
        $applicableTaxes = $taxRule->getTaxes()->toArray();
        $resolvedTaxes = [];
        foreach ($taxGroup->getTaxes() as $tax) {
            if (in_array($tax, $applicableTaxes, true)) {
                $resolvedTaxes[] = $tax;
            }
        }

        // Caches the resolved taxes
        $this->cache->set($taxGroup, $country, $business, $resolvedTaxes);

        return $resolvedTaxes;
    }

    /**
     * Resolves the sale tax rule.
     *
     * @param SaleInterface $sale
     *
     * @return TaxRuleInterface|null
     */
    public function resolveSaleTaxRule(SaleInterface $sale): ?TaxRuleInterface
    {
        return $this->resolveTaxRule($this->resolveTargetCountry($sale), $sale->isBusiness());
    }

    /**
     * Resolves the target country.
     *
     * @param mixed $target
     *
     * @return CountryInterface
     */
    protected function resolveTargetCountry($target): CountryInterface
    {
        if (null === $target) {
            return $this->countryProvider->getCountry();
        }

        if ($target instanceof CountryInterface) {
            return $target;
        }

        if ($target instanceof SaleInterface) {
            $country = $this->resolveSaleTargetCountry($target);
        } elseif ($target instanceof CustomerInterface) {
            $country = $this->resolveCustomerTargetCountry($target);
        } elseif(is_string($target) && 2 == strlen($target)) {
            $country = $this->getCountryByCode($target);
        } else {
            throw new InvalidArgumentException("Unexpected taxation target.");
        }

        return $country ?: $this->countryProvider->getCountry();
    }

    /**
     * Resolves the tax rule for the given sale.
     *
     * @param CountryInterface $country
     * @param bool             $business
     *
     * @return TaxRuleInterface|null
     */
    protected function resolveTaxRule(CountryInterface $country, $business = false): ?TaxRuleInterface
    {
        if ($business) {
            return $this->taxRuleRepository->findOneByCountryForBusiness($country);
        }

        return $this->taxRuleRepository->findOneByCountryForCustomer($country);
    }

    /**
     * Resolves the sales's taxation target country.
     *
     * @param SaleInterface $sale
     *
     * @return CountryInterface|null
     */
    protected function resolveSaleTargetCountry(SaleInterface $sale): ?CountryInterface
    {
        // Get the country from the sale's delivery address
        if (null !== $country = $sale->getDeliveryCountry()) {
            return $country;
        }

        // If none, resolves the customer's taxation target address
        if (null !== $customer = $sale->getCustomer()) {
            return $this->resolveCustomerTargetCountry($customer);
        }

        return null;
    }

    /**
     * Resolves the customer's taxation target country.
     *
     * @param CustomerInterface $customer
     *
     * @return CountryInterface|null
     */
    protected function resolveCustomerTargetCountry(CustomerInterface $customer): ?CountryInterface
    {
        if (null !== $address = $customer->getDefaultDeliveryAddress()) {
            return $address->getCountry();
        }

        return null;
    }

    /**
     * Returns the country by its code.
     *
     * @param string $code
     *
     * @return CountryInterface|null
     */
    protected function getCountryByCode(string $code): ?CountryInterface
    {
        return $this->countryProvider->getCountry($code);
    }
}
