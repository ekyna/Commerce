<?php

namespace Ekyna\Component\Commerce\Pricing\Repository;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface TaxRuleRepositoryInterface
 * @package Ekyna\Component\Commerce\Pricing\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TaxRuleRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Returns the tax rule by its code.
     *
     * @param string $code
     *
     * @return TaxRuleInterface|null
     */
    public function findOneByCode(string $code): ?TaxRuleInterface;

    /**
     * Finds the tax rule by country for customer.
     *
     * @param CountryInterface $source
     * @param CountryInterface $target
     *
     * @return TaxRuleInterface|null
     */
    public function findOneForCustomer(CountryInterface $source, CountryInterface $target): ?TaxRuleInterface;

    /**
     * Finds the tax rule by country for business.
     *
     * @param CountryInterface $source
     * @param CountryInterface $target
     *
     * @return TaxRuleInterface|null
     */
    public function findOneForBusiness(CountryInterface $source, CountryInterface $target): ?TaxRuleInterface;
}
