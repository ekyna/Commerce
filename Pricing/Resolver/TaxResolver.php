<?php

namespace Ekyna\Component\Commerce\Pricing\Resolver;

use Ekyna\Component\Commerce\Common\Context\ContextInterface as Context;
use Ekyna\Component\Commerce\Common\Country\CountryProviderInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface as Country;
use Ekyna\Component\Commerce\Common\Model\SaleInterface as Sale;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface as Taxable;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface as TaxRule;
use Ekyna\Component\Commerce\Pricing\Repository\TaxRuleRepositoryInterface;
use Ekyna\Component\Commerce\Stock\Provider\WarehouseProviderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface as SupplierOrder;

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
     * @var WarehouseProviderInterface
     */
    protected $warehouseProvider;

    /**
     * @var TaxRuleRepositoryInterface
     */
    protected $taxRuleRepository;

    /**
     * @var ResolvedTaxesCache
     */
    protected $cache;


    /**
     * Constructor.
     *
     * @param CountryProviderInterface   $countryProvider
     * @param WarehouseProviderInterface $warehouseProvider
     * @param TaxRuleRepositoryInterface $taxRuleRepository
     */
    public function __construct(
        CountryProviderInterface $countryProvider,
        WarehouseProviderInterface $warehouseProvider,
        TaxRuleRepositoryInterface $taxRuleRepository
    ) {
        $this->countryProvider = $countryProvider;
        $this->warehouseProvider = $warehouseProvider;
        $this->taxRuleRepository = $taxRuleRepository;

        $this->setCache();
    }

    /**
     * @inheritDoc
     */
    public function setCache(ResolvedTaxesCache $cache = null): void
    {
        $this->cache = $cache ?? new ResolvedTaxesCache();
    }

    /**
     * @inheritDoc
     */
    public function resolveTaxes(Taxable $taxable, $context): array
    {
        /** @see https://ec.europa.eu/taxation_customs/business/vat/eu-vat-rules-topic/where-tax_fr */
        /** @see https://en.wikipedia.org/wiki/International_taxation#Taxation_systems */

        // Abort if taxable does not have tax group
        if (null === $taxGroup = $taxable->getTaxGroup()) {
            return [];
        }

        // Abort if tax group does not have taxes
        if (!$taxGroup->hasTaxes()) {
            return [];
        }

        // Resolve source and target countries
        $source = $this->resolveSourceCountry($context);
        $target = $this->resolveTargetCountry($context);
        // Resolve whether it is for business
        $business = $this->resolveBusiness($context);

        // Use cached resolved taxes if any
        if (null !== $taxes = $this->cache->get($taxGroup, $source, $target, $business)) {
            return $taxes;
        }

        // Do resolution
        $taxes = $this->resolve($taxable, $source, $target, $business);

        // Caches the resolved taxes
        $this->cache->set($taxGroup, $target, $source, $business, $taxes);

        return $taxes;
    }

    /**
     * Resolves the sale tax rule.
     *
     * @param Sale $sale
     *
     * @return TaxRule|null
     */
    public function resolveSaleTaxRule(Sale $sale): ?TaxRule
    {
        return $this->resolveTaxRule(
            $this->resolveSourceCountry($sale),
            $this->resolveTargetCountry($sale),
            $sale->isBusiness()
        );
    }

    /**
     * Resolves the target country.
     *
     * @param Context|Sale|SupplierOrder|null $context
     *
     * @return Country
     */
    protected function resolveSourceCountry($context): Country
    {
        if (is_null($context)) {
            return $this->countryProvider->getCountryRepository()->findDefault();
        }

        if ($context instanceof Context) {
            return $context->getShippingCountry();
        }

        if ($context instanceof Sale) {
            return $this->resolveSaleSourceCountry($context);
        }

        if ($context instanceof SupplierOrder) {
            return $context->getSupplier()->getAddress()->getCountry();
        }

        throw new UnexpectedTypeException($context, [
            Context::class,
            Sale::class,
            SupplierOrder::class,
            'null',
        ]);
    }

    /**
     * Resolves the target country.
     *
     * @param Context|Sale|SupplierOrder|null $context
     *
     * @return Country
     */
    protected function resolveTargetCountry($context): Country
    {
        if (is_null($context)) {
            return $this->countryProvider->getCountry();
        }

        if ($context instanceof Context) {
            return $context->getDeliveryCountry();
        }

        if ($context instanceof Sale) {
            return $this->resolveSaleTargetCountry($context);
        }

        if ($context instanceof SupplierOrder) {
            return $context->getWarehouse()->getCountry();
        }

        throw new UnexpectedTypeException($context, [Context::class, Sale::class, SupplierOrder::class, 'null']);
    }

    /**
     * Resolves whether the given context is for business.
     *
     * @param Context|Sale|SupplierOrder|null $context
     *
     * @return bool
     */
    protected function resolveBusiness($context): bool
    {
        if (is_null($context)) {
            return false;
        }

        if ($context instanceof Context) {
            return $context->isBusiness();
        }

        if ($context instanceof Sale) {
            return $context->isBusiness();
        }

        if ($context instanceof SupplierOrder) {
            return true;
        }

        throw new UnexpectedTypeException($context, [Context::class, Sale::class, SupplierOrder::class, 'null']);
    }

    /**
     * Performs the taxes resolution.
     *
     * @param Taxable $taxable
     * @param Country $source
     * @param Country $target
     * @param bool    $business
     *
     * @return array
     */
    protected function resolve(Taxable $taxable, Country $source, Country $target, bool $business): array
    {
        // Abort if no matching tax rul
        if (null === $taxRule = $this->resolveTaxRule($source, $target, $business)) {
            return [];
        }

        // Abort if tax rule does not have taxes
        if (!$taxRule->hasTaxes()) {
            return [];
        }

        $resolved = [];

        $applicable = $taxRule->getTaxes()->toArray();

        foreach ($taxable->getTaxGroup()->getTaxes() as $tax) {
            if (in_array($tax, $applicable, true)) {
                $resolved[] = $tax;
            }
        }

        return $resolved;
    }

    /**
     * Resolves the tax rule for the given sale.
     *
     * @param Country $target
     * @param Country $source
     * @param bool    $business
     *
     * @return TaxRule|null
     */
    protected function resolveTaxRule(Country $source, Country $target, bool $business): ?TaxRule
    {
        if ($business) {
            return $this->taxRuleRepository->findOneForBusiness($source, $target);
        }

        return $this->taxRuleRepository->findOneForCustomer($source, $target);
    }

    /**
     * Resolves the sales's taxation target country.
     *
     * @param Sale $sale
     *
     * @return Country
     */
    protected function resolveSaleSourceCountry(Sale $sale): Country
    {
        return $this
            ->warehouseProvider
            ->getWarehouse($sale->getDeliveryCountry())
            ->getCountry();
    }

    /**
     * Resolves the sales's taxation target country.
     *
     * @param Sale $sale
     *
     * @return Country
     */
    protected function resolveSaleTargetCountry(Sale $sale): Country
    {
        // Get the country from the sale's delivery address
        if ($country = $sale->getDeliveryCountry()) {
            return $country;
        }

        // If none, resolves the customer's taxation target address
        if (($customer = $sale->getCustomer()) && ($address = $customer->getDefaultDeliveryAddress())) {
            return $address->getCountry();
        }

        return $this->countryProvider->getCountry();
    }
}
