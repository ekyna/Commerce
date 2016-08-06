<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface StateInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StateInterface extends EntityInterface
{
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
     * @return $this|StateInterface
     */
    public function setCountry(CountryInterface $country);

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
     * @return $this|StateInterface
     */
    public function setName($name);

    /**
     * Returns the code.
     *
     * @return string
     */
    public function getCode();

    /**
     * Sets the code.
     *
     * @param string $code
     * @return $this|StateInterface
     */
    public function setCode($code);
}
