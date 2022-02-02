<?php

namespace Ekyna\Component\Commerce\Accounting\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Accounting\Model\AccountingInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\IsEnabledTrait;
use Ekyna\Component\Resource\Model\SortableTrait;

/**
 * Class Account
 * @package Ekyna\Component\Commerce\Accounting\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Accounting extends AbstractResource implements AccountingInterface
{
    use SortableTrait;
    use IsEnabledTrait;

    /**
     * @var string
     */
    private $number;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var TaxRuleInterface
     */
    private $taxRule;

    /**
     * @var TaxInterface
     */
    private $tax;

    /**
     * @var PaymentMethodInterface
     */
    private $paymentMethod;

    /**
     * @var ArrayCollection|CustomerGroupInterface[]
     */
    private $customerGroups;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->customerGroups = new ArrayCollection();
    }

    /**
     * Returns a string representation
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name ?: 'New accounting';
    }

    /**
     * @inheritDoc
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @inheritDoc
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTaxRule()
    {
        return $this->taxRule;
    }

    /**
     * @inheritDoc
     */
    public function setTaxRule(TaxRuleInterface $taxRule = null)
    {
        $this->taxRule = $taxRule;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @inheritDoc
     */
    public function setTax(TaxInterface $tax = null)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @inheritDoc
     */
    public function setPaymentMethod(PaymentMethodInterface $method = null)
    {
        $this->paymentMethod = $method;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCustomerGroups()
    {
        return $this->customerGroups;
    }

    /**
     * @inheritDoc
     */
    public function addCustomerGroup(CustomerGroupInterface $group)
    {
        if (!$this->customerGroups->contains($group)) {
            $this->customerGroups->add($group);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeCustomerGroup(CustomerGroupInterface $group)
    {
        if ($this->customerGroups->contains($group)) {
            $this->customerGroups->removeElement($group);
        }

        return $this;
    }
}
