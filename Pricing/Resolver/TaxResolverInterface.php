<?php

namespace Ekyna\Component\Commerce\Pricing\Resolver;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;

/**
 * Interface TaxResolverInterface
 * @package Ekyna\Component\Commerce\Pricing\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TaxResolverInterface
{
    /**
     * Resolves the taxable's taxes.
     *
     * @param TaxableInterface $taxable
     * @param mixed            $target
     *
     * @return array|TaxInterface[]
     */
    public function resolveTaxes(TaxableInterface $taxable, $target = null);

    /**
     * Resolves the sale's tax rule.
     *
     * @param SaleInterface $sale
     *
     * @return \Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface|null
     */
    public function resolveSaleTaxRule(SaleInterface $sale);
}
