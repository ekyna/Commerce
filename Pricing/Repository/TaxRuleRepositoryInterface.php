<?php

namespace Ekyna\Component\Commerce\Pricing\Repository;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface TaxRuleRepositoryInterface
 * @package Ekyna\Component\Commerce\Pricing\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TaxRuleRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the tax rule by country for customer.
     *
     * @param CountryInterface  $country
     *
     * @return \Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface|null
     */
    public function findOneByCountryForCustomer(
        CountryInterface $country
    );

    /**
     * Finds the tax rule by country for business.
     *
     * @param CountryInterface  $country
     *
     * @return \Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface|null
     */
    public function findOneByCountryForBusiness(
        CountryInterface $country
    );
}
