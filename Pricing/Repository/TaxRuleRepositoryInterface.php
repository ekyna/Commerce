<?php

namespace Ekyna\Component\Commerce\Pricing\Repository;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
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
     * Finds tax rules by tax group, customer group and country.
     *
     * @param TaxGroupInterface      $taxGroup
     * @param CustomerGroupInterface $customerGroup
     * @param CountryInterface       $country
     *
     * @return array|TaxRuleInterface[]
     */
    public function findByTaxGroupAndCustomerGroupAndCountry(
        TaxGroupInterface $taxGroup,
        CustomerGroupInterface $customerGroup,
        CountryInterface $country
    );
}
