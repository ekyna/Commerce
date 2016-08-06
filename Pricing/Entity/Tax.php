<?php

namespace Ekyna\Component\Commerce\Pricing\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\StateInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;

/**
 * Class Total
 * @package Ekyna\Component\Commerce\Pricing\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Tax implements TaxInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var float
     */
    protected $rate;

    /**
     * @var CountryInterface
     */
    protected $country;

    /**
     * @var StateInterface
     */
    protected $state;

    /**
     * @var string
     */
    protected $postalCodeMatch;

    /**
     * @var ArrayCollection|TaxRuleInterface[]
     */
    protected $taxRules;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->rate = 0;
        $this->postalCodeMatch = '*';
        $this->taxRules = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
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
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @inheritdoc
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @inheritdoc
     */
    public function setCountry(CountryInterface $country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @inheritdoc
     */
    public function setState(StateInterface $state = null)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPostalCodeMatch()
    {
        return $this->postalCodeMatch;
    }

    /**
     * @inheritdoc
     */
    public function setPostalCodeMatch($postalCodeMatch)
    {
        $this->postalCodeMatch = $postalCodeMatch;

        return $this;
    }


    /**
     * @inheritdoc
     */
    public function hasTaxRules()
    {
        return 0 < $this->taxRules->count();
    }

    /**
     * @inheritdoc
     */
    public function getTaxRules()
    {
        return $this->taxRules;
    }

    /**
     * @inheritdoc
     */
    public function hasTaxRule(TaxRuleInterface $taxRule)
    {
        return $this->taxRules->contains($taxRule);
    }

    /**
     * @inheritdoc
     */
    public function addTaxRule(TaxRuleInterface $taxRule)
    {
        if (!$this->hasTaxRule($taxRule)) {
            $this->taxRules->add($taxRule);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeTaxRule(TaxRuleInterface $taxRule)
    {
        if ($this->hasTaxRule($taxRule)) {
            $this->taxRules->removeElement($taxRule);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTaxRules(ArrayCollection $taxRules)
    {
        /** @var \Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface $taxRule */
        foreach ($taxRules as $taxRule) {
            $taxRule->addTax($this);
        }

        return $this;
    }
}
