<?php

namespace Ekyna\Component\Commerce\Pricing\Repository;

use Ekyna\Component\Commerce\Address\Model\AddressInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;

/**
 * Interface TaxRuleRepositoryInterface
 * @package Ekyna\Component\Commerce\Pricing\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TaxRuleRepositoryInterface
{
    /**
     * Finds tax rules by tax group and customer groups.
     *
     * @param TaxGroupInterface              $taxGroup
     * @param array|CustomerGroupInterface[] $customerGroups
     * @param AddressInterface               $address
     *
     * @return array|TaxRuleInterface[]
     */
    public function findByTaxGroupAndCustomerGroups(
        TaxGroupInterface $taxGroup,
        array $customerGroups,
        AddressInterface $address
    );
}
