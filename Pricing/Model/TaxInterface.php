<?php

namespace Ekyna\Component\Commerce\Pricing\Model;

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
     *
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
     *
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
     *
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
     *
     * @return $this|TaxInterface
     */
    public function setState(StateInterface $state = null);
}
