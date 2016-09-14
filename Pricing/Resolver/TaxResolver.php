<?php

namespace Ekyna\Component\Commerce\Pricing\Resolver;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Pricing\Repository\TaxRuleRepositoryInterface;

/**
 * Class TaxResolver
 * @package Ekyna\Component\Commerce\Pricing\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxResolver implements TaxResolverInterface
{
    /**
     * @var CustomerGroupRepositoryInterface
     */
    protected $customerGroupRepository;

    /**
     * @var CountryRepositoryInterface
     */
    protected $countryRepository;

    /**
     * @var TaxRuleRepositoryInterface
     */
    protected $taxRuleRepository;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var AddressInterface
     */
    protected $originAddress;


    /**
     * @inheritdoc
     */
    public static function getAvailableModes()
    {
        return [static::BY_INVOICE, static::BY_DELIVERY, static::BY_ORIGIN];
    }

    /**
     * Constructor.
     *
     * @param CustomerGroupRepositoryInterface $customerGroupRepository
     * @param CountryRepositoryInterface       $countryRepository
     * @param TaxRuleRepositoryInterface       $taxRuleRepository
     * @param string                           $mode
     */
    public function __construct(
        CustomerGroupRepositoryInterface $customerGroupRepository,
        CountryRepositoryInterface $countryRepository,
        TaxRuleRepositoryInterface $taxRuleRepository,
        $mode = TaxResolverInterface::BY_DELIVERY
    ) {
        $this->customerGroupRepository = $customerGroupRepository;
        $this->countryRepository = $countryRepository;
        $this->taxRuleRepository = $taxRuleRepository;

        $this->setMode($mode);
    }

    /**
     * @inheritdoc
     */
    public function setMode($mode)
    {
        if (!in_array($mode, static::getAvailableModes(), true)) {
            throw new InvalidArgumentException("Unexpected mode '{$mode}'.");
        }

        $this->mode = $mode;
    }

    /**
     * @inheritdoc
     */
    public function setOriginAddress(AddressInterface $address)
    {
        $this->originAddress = $address;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultTaxesBySubject(TaxableInterface $taxable)
    {
        if (null === $taxGroup = $taxable->getTaxGroup()) {
            return [];
        }

        return $this->getApplicableTaxesByTaxGroupAndCustomerGroups($taxGroup, [$this->getDefaultCustomerGroup()]);
    }

    /**
     * @inheritdoc
     */
    public function getApplicableTaxesBySubjectAndCustomer(
        TaxableInterface $taxable,
        CustomerInterface $customer,
        AddressInterface $address = null
    ) {
        if (null === $taxGroup = $taxable->getTaxGroup()) {
            return [];
        }

        // TODO What if customer groups is empty :s ?

        return $this->getApplicableTaxesByTaxGroupAndCustomerGroups(
            $taxGroup,
            $customer->getCustomerGroups()->toArray(),
            $address
        );
    }

    /**
     * @inheritdoc
     */
    public function getApplicableTaxesByTaxGroupAndCustomerGroups(
        TaxGroupInterface $taxGroup,
        array $customerGroups,
        AddressInterface $address = null
    ) {
        if ($this->mode === static::BY_ORIGIN) {
            if (null === $this->originAddress) {
                throw new \RuntimeException("Mode is set to 'origin' but origin address is not set.");
            }
            $country = $this->originAddress->getCountry();
        } else if (null !== $address) {
            $country = $address->getCountry();
        } else {
            $country = $this->getDefaultCountry();
        }

        $taxes = [];
        $rules = $this
            ->taxRuleRepository
            ->findByTaxGroupAndCustomerGroupsAndCountry($taxGroup, $customerGroups, $country);

        foreach ($rules as $rule) {
            foreach ($rule->getTaxes() as $tax) {
                if ($tax->getCountry() === $country && !in_array($tax, $taxes, true)) {
                    array_push($taxes, $tax);
                }
            }
        }

        return $taxes;
    }

    /**
     * Returns the default customer group.
     *
     * @return \Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface
     */
    private function getDefaultCustomerGroup()
    {
        return $this->customerGroupRepository->findDefault();
    }

    /**
     * Returns the default country.
     *
     * @return CountryInterface
     */
    private function getDefaultCountry()
    {
        return $this->countryRepository->findDefault();
    }
}
