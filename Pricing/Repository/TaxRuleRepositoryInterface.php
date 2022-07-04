<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Pricing\Repository;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface TaxRuleRepositoryInterface
 * @package Ekyna\Component\Commerce\Pricing\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<TaxRuleInterface>
 */
interface TaxRuleRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns the tax rule by its code.
     */
    public function findOneByCode(string $code): ?TaxRuleInterface;

    /**
     * Finds the tax rule by country for customer.
     */
    public function findOneForCustomer(CountryInterface $source, CountryInterface $target): ?TaxRuleInterface;

    /**
     * Finds the tax rule by country for business.
     */
    public function findOneForBusiness(CountryInterface $source, CountryInterface $target): ?TaxRuleInterface;
}
