<?php

namespace Ekyna\Component\Commerce\Pricing\Resolver;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Interface TaxResolverInterface
 * @package Ekyna\Component\Commerce\Pricing\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TaxResolverInterface
{
    const BY_INVOICE  = 'invoice';
    const BY_DELIVERY = 'delivery';
    const BY_ORIGIN   = 'origin';

    /**
     * Returns the available modes.
     *
     * @return array
     */
    public static function getAvailableModes();

    /**
     * Sets the resolution mode.
     *
     * @param string $mode
     *
     * @return $this|TaxResolverInterface
     */
    public function setMode($mode);

    /**
     * Sets the origin address.
     *
     * @param AddressInterface $address
     *
     * @return $this|TaxResolverInterface
     */
    public function setOriginAddress(AddressInterface $address);

    /**
     * Resolves the applicable taxes by subject and customer.
     *
     * @param SubjectInterface  $subject
     * @param CustomerInterface $customer
     * @param AddressInterface  $address
     *
     * @return array|TaxInterface[]
     */
    public function getApplicableTaxesBySubjectAndCustomer(
        SubjectInterface $subject,
        CustomerInterface $customer,
        AddressInterface $address = null
    );

    /**
     * Resolves the applicable taxes by tax group and customer group.
     *
     * @param TaxGroupInterface              $taxGroup
     * @param array|CustomerGroupInterface[] $customerGroups
     * @param AddressInterface               $address
     *
     * @return array|TaxInterface[]
     */
    public function getApplicableTaxesByTaxGroupAndCustomerGroups(
        TaxGroupInterface $taxGroup,
        array $customerGroups,
        AddressInterface $address = null
    );
}
