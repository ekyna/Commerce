<?php

namespace Ekyna\Component\Commerce\Pricing\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\AdjustmentDataInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\StateInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface TaxInterface
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TaxInterface extends AdjustmentDataInterface, ResourceInterface
{
    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the name.
     *
     * @param string $name
     * @return $this|TaxInterface
     */
    public function setName($name);

    /**
     * Returns the rate.
     *
     * @return float
     */
    public function getRate();

    /**
     * Sets the rate.
     *
     * @param float $rate
     * @return $this|TaxInterface
     */
    public function setRate($rate);

    /**
     * Returns the country.
     *
     * @return CountryInterface
     */
    public function getCountry();

    /**
     * Sets the country.
     *
     * @param CountryInterface $country
     * @return $this|TaxInterface
     */
    public function setCountry(CountryInterface $country);

    /**
     * Returns the state.
     *
     * @return StateInterface
     */
    public function getState();

    /**
     * Sets the state.
     *
     * @param StateInterface $state
     * @return $this|TaxInterface
     */
    public function setState(StateInterface $state = null);

    /**
     * Returns the postalCodeMatch.
     *
     * @return string
     */
    public function getPostalCodeMatch();

    /**
     * Sets the postalCodeMatch.
     *
     * @param string $postalCodeMatch
     * @return $this|TaxInterface
     */
    public function setPostalCodeMatch($postalCodeMatch);

    /**
     * Returns whether the tax has tax rules or not.
     *
     * @return bool
     */
    public function hasTaxRules();

    /**
     * Returns the tax rules.
     *
     * @return ArrayCollection|TaxRuleInterface[]
     */
    public function getTaxRules();

    /**
     * Returns whether the tax has the tax rule or not.
     *
     * @param TaxRuleInterface $taxRule
     * @return bool
     */
    public function hasTaxRule(TaxRuleInterface $taxRule);

    /**
     * Adds the tax rule.
     *
     * @param TaxRuleInterface $taxRule
     * @return $this|TaxInterface
     */
    public function addTaxRule(TaxRuleInterface $taxRule);

    /**
     * Removes the tax rule.
     *
     * @param TaxRuleInterface $taxRule
     * @return $this|TaxInterface
     */
    public function removeTaxRule(TaxRuleInterface $taxRule);

    /**
     * Sets the tax rules.
     *
     * @param ArrayCollection|TaxRuleInterface[] $taxRules
     * @return $this|TaxInterface
     */
    public function setTaxRules(ArrayCollection $taxRules);
}
