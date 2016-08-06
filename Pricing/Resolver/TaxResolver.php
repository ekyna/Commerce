<?php

namespace Ekyna\Component\Commerce\Pricing\Resolver;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Pricing\Repository\TaxRuleRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Class TaxResolver
 * @package Ekyna\Component\Commerce\Pricing\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxResolver implements TaxResolverInterface
{
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
     * @param TaxRuleRepositoryInterface $taxRuleRepository
     * @param string                     $mode
     */
    public function __construct(
        TaxRuleRepositoryInterface $taxRuleRepository,
        $mode = TaxResolverInterface::BY_DELIVERY
    ) {
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
    public function getApplicableTaxesBySubjectAndCustomer(
        SubjectInterface $subject,
        CustomerInterface $customer,
        AddressInterface $address = null
    ) {
        return $this->getApplicableTaxesByTaxGroupAndCustomerGroups(
            $subject->getTaxGroup(),
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
            $targetAddress = $this->originAddress;
        } else if (null === $address) {
            throw new InvalidArgumentException('Expected ' . AddressInterface::class);
        } else {
            $targetAddress = $address;
        }

        $taxes = [];
        $rules = $this->taxRuleRepository->findByTaxGroupAndCustomerGroups($taxGroup, $customerGroups, $targetAddress);

        foreach ($rules as $rule) {
            foreach ($rule->getTaxes() as $tax) {
                if ($tax->getCountry() === $targetAddress->getCountry()
                    && !in_array($tax, $taxes, true)) {
                    array_push($taxes, $tax);
                }
            }
        }

        return $taxes;
    }

}
