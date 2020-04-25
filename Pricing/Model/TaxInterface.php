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
     * Returns the code.
     *
     * @return string
     */
    public function getCode(): ?string;

    /**
     * Sets the code.
     *
     * @param string $code
     *
     * @return $this|TaxInterface
     */
    public function setCode(string $code): TaxInterface;

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName(): ?string;

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return $this|TaxInterface
     */
    public function setName(string $name): TaxInterface;

    /**
     * Returns the rate.
     *
     * @return float
     */
    public function getRate(): float;

    /**
     * Sets the rate.
     *
     * @param float $rate
     *
     * @return $this|TaxInterface
     */
    public function setRate(float $rate): TaxInterface;

    /**
     * Returns the country.
     *
     * @return CountryInterface
     */
    public function getCountry(): ?CountryInterface;

    /**
     * Sets the country.
     *
     * @param CountryInterface $country
     *
     * @return $this|TaxInterface
     */
    public function setCountry(CountryInterface $country): TaxInterface;

    /**
     * Returns the state.
     *
     * @return StateInterface
     */
    public function getState(): ?StateInterface;

    /**
     * Sets the state.
     *
     * @param StateInterface $state
     *
     * @return $this|TaxInterface
     */
    public function setState(StateInterface $state = null): TaxInterface;
}
