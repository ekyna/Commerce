<?php

namespace Ekyna\Component\Commerce\Pricing\Resolver;

use Doctrine\Common\Collections\Criteria;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
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
    public function resolveTaxesBySale(TaxableInterface $taxable, SaleInterface $sale)
    {
        // Abort if taxable has no tax group
        if (null === $taxGroup = $taxable->getTaxGroup()) {
            return [];
        }

        // Abort if sale is tax exempt
        if ($sale->isTaxExempt()) {
            return [];
        }

        // Resolve customer group (sale customer group has precedence)
        $customer = $sale->getCustomer();
        if ((null === $customerGroup = $sale->getCustomerGroup()) && null !== $customer) {
            $customerGroup = $customer->getCustomerGroup();
        }

        // Resolve the taxation target country
        $country = $address = null;
        if (null === $address && $this->mode != static::BY_ORIGIN) {
            // Resolve sale's taxation target address
            if ($this->mode === static::BY_DELIVERY) {
                $address = $sale->isSameAddress() ? $sale->getInvoiceAddress() : $sale->getDeliveryAddress();
            } elseif ($this->mode === static::BY_INVOICE) {
                $address = $sale->getInvoiceAddress();
            }
            // Customer fallback address
            if (null === $address && null !== $customer) {
                $address = $this->getCustomerFallbackAddress($customer);
            }
        }
        // Get the country from the resolved address
        if (null !== $address) {
            $country = $address->getCountry();
        }

        // Resolve taxes
        return $this->getTaxesByTaxGroupAndCustomerGroupAndCountry(
            $taxGroup,
            $customerGroup,
            $country
        );
    }

    /**
     * @inheritdoc
     */
    public function resolveTaxesByCustomerAndAddress(
        TaxableInterface $taxable,
        CustomerInterface $customer,
        AddressInterface $address = null
    ) {
        // Abort if taxable has no tax group
        if (null === $taxGroup = $taxable->getTaxGroup()) {
            return [];
        }

        // Resolve the taxation target country
        $country = null;
        // If address is null, get the customer fallback address
        if (null === $address && $this->mode != static::BY_ORIGIN) {
            $address = $this->getCustomerFallbackAddress($customer);
        }
        if (null !== $address) {
            $country = $address->getCountry();
        }

        // Resolve taxes
        return $this->getTaxesByTaxGroupAndCustomerGroupAndCountry(
            $taxGroup,
            $customer->getCustomerGroup(),
            $country
        );
    }

    /**
     * @inheritdoc
     */
    public function resolveDefaultTaxes(TaxableInterface $taxable)
    {
        if (null === $taxGroup = $taxable->getTaxGroup()) {
            return [];
        }

        return $this->getTaxesByTaxGroupAndCustomerGroupAndCountry($taxGroup);
    }

    /**
     * @inheritdoc
     */
    public function getTaxesByTaxGroupAndCustomerGroupAndCountry(
        TaxGroupInterface $taxGroup,
        CustomerGroupInterface $customerGroup = null,
        CountryInterface $country = null
    ) {
        // Resolves taxation target country
        if ($this->mode === static::BY_ORIGIN) {
            if (null === $this->originAddress) {
                throw new \RuntimeException("Mode is set to 'origin' but origin address is not set.");
            }
            $country = $this->originAddress->getCountry();
        } elseif (null === $country) {
            $country = $this->getFallbackCountry();
        }

        // Fallback customer group
        if (null === $customerGroup) {
            $customerGroup = $this->getFallbackCustomerGroup();
        }

        // Find tax rules
        $taxes = [];
        $rules = $this
            ->taxRuleRepository
            ->findByTaxGroupAndCustomerGroupAndCountry($taxGroup, $customerGroup, $country);

        // Extract the taxes that matches the country from the tax rules
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
     * Returns the customer's fallback address.
     *
     * @param CustomerInterface $customer
     *
     * @return AddressInterface|null
     */
    private function getCustomerFallbackAddress(CustomerInterface $customer)
    {
        $criteria = new Criteria([], [], 0, 1);
        if ($this->mode === static::BY_INVOICE) {
            $criteria->where(Criteria::expr()->eq('invoiceDefault', true));
        } elseif ($this->mode === static::BY_DELIVERY) {
            $criteria->where(Criteria::expr()->eq('deliveryDefault', true));
        }

        $results = $customer->getAddresses()->matching($criteria);
        if (1 == $results->count()) {
            return $results->first();
        }

        return null;
    }

    /**
     * Returns the fallback customer group.
     *
     * @return \Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface
     */
    private function getFallbackCustomerGroup()
    {
        return $this->customerGroupRepository->findDefault();
    }

    /**
     * Returns the fallback country.
     *
     * @return CountryInterface
     */
    private function getFallbackCountry()
    {
        return $this->countryRepository->findDefault();
    }
}
