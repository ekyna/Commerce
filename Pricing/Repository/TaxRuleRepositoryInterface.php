<?php

namespace Ekyna\Component\Commerce\Pricing\Repository;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
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
