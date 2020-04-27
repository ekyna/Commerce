<?php

namespace Ekyna\Component\Commerce\Pricing\Resolver;

use Ekyna\Component\Commerce\Common\Context\ContextInterface as Context;
use Ekyna\Component\Commerce\Common\Model\SaleInterface as Sale;
use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface as Taxable;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface as Tax;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface as TaxRule;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface as SupplierOrder;

/**
 * Interface TaxResolverInterface
 * @package Ekyna\Component\Commerce\Pricing\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TaxResolverInterface
{
    /**
     * Sets the resolved taxes cache.
     *
     * @param ResolvedTaxesCache|null $cache
     */
    public function setCache(ResolvedTaxesCache $cache = null): void;

    /**
     * Resolves the taxable's taxes.
     *
     * @param Taxable                         $taxable
     * @param Context|Sale|SupplierOrder|null $context
     *
     * @return Tax[]
     */
    public function resolveTaxes(Taxable $taxable, $context = null): array;

    /**
     * Resolves the sale tax rule.
     *
     * @param Sale $sale
     *
     * @return TaxRule|null
     */
    public function resolveSaleTaxRule(Sale $sale): ?TaxRule;
}
