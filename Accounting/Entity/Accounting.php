<?php

namespace Ekyna\Component\Commerce\Accounting\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Accounting\Model\AccountingInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;
use Ekyna\Component\Resource\Model\IsEnabledTrait;
use Ekyna\Component\Resource\Model\SortableTrait;

/**
 * Class Account
 * @package Ekyna\Component\Commerce\Accounting\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Accounting implements AccountingInterface
{
    use SortableTrait;
    use IsEnabledTrait;

    /**
     * @var int
     */
    private $id;

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
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @inheritdoc
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTaxRule()
    {
        return $this->taxRule;
    }

    /**
     * @inheritdoc
     */
    public function setTaxRule(TaxRuleInterface $taxRule = null)
    {
        $this->taxRule = $taxRule;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @inheritdoc
     */
    public function setTax(TaxInterface $tax = null)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @inheritdoc
     */
    public function setPaymentMethod(PaymentMethodInterface $method = null)
    {
        $this->paymentMethod = $method;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroups()
    {
        return $this->customerGroups;
    }

    /**
     * @inheritdoc
     */
    public function addCustomerGroup(CustomerGroupInterface $group)
    {
        if (!$this->customerGroups->contains($group)) {
            $this->customerGroups->add($group);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeCustomerGroup(CustomerGroupInterface $group)
    {
        if ($this->customerGroups->contains($group)) {
            $this->customerGroups->removeElement($group);
        }

        return $this;
    }
}
