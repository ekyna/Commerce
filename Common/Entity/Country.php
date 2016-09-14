<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\StateInterface;

/**
 * Class Country
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Country implements CountryInterface
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
     * @var string
     */
    protected $code;

    /**
     * @var boolean
     */
    protected $enabled;

    /**
     * @var boolean
     */
    protected $default;

    /**
     * @var ArrayCollection|StateInterface[]
     */
    protected $states;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->states = new ArrayCollection();
        $this->enabled = true;
        $this->default = false;
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
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @inheritdoc
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @inheritdoc
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool)$enabled;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @inheritdoc
     */
    public function setDefault($default)
    {
        $this->default = (bool)$default;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * @inheritdoc
     */
    public function hasState(StateInterface $state)
    {
        return $this->states->contains($state);
    }

    /**
     * @inheritdoc
     */
    public function addState(StateInterface $state)
    {
        if (!$this->hasState($state)) {
            $state->setCountry($this);
            $this->states->add($state);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeState(StateInterface $state)
    {
        if ($this->hasState($state)) {
            $state->setCountry(null);
            $this->states->removeElement($state);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setStates(ArrayCollection $states)
    {
        $this->states = $states;

        return $this;
    }
}
