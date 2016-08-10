<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface CountryInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CountryInterface extends ResourceInterface
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
     * @return $this|CountryInterface
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
     * @return $this|CountryInterface
     */
    public function setCode($code);

    /**
     * Returns whether the country is enabled or not.
     *
     * @return boolean
     */
    public function isEnabled();

    /**
     * Sets the enabled.
     *
     * @param boolean $enabled
     * @return $this|CountryInterface
     */
    public function setEnabled($enabled);

    /**
     * Returns the states.
     *
     * @return ArrayCollection|StateInterface[]
     */
    public function getStates();

    /**
     * Returns whether the country has the state or not.
     *
     * @param StateInterface $state
     * @return bool
     */
    public function hasState(StateInterface $state);

    /**
     * Adds the state.
     *
     * @param StateInterface $state
     * @return $this|CountryInterface
     */
    public function addState(StateInterface $state);

    /**
     * Removes the state.
     *
     * @param StateInterface $state
     * @return $this|CountryInterface
     */
    public function removeState(StateInterface $state);

    /**
     * Sets the states.
     *
     * @param ArrayCollection|StateInterface[] $states
     * @return $this|CountryInterface
     */
    public function setStates(ArrayCollection $states);
}
