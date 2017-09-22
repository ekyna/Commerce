<?php

namespace Ekyna\Component\Commerce\Customer\Entity;

use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;

/**
 * Class Group
 * @package Ekyna\Component\Commerce\Customer\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroup implements CustomerGroupInterface
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
     * @var boolean
     */
    protected $default;

    /**
     * @var boolean
     */
    protected $business;

    /**
     * @var boolean
     */
    protected $registration;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->default = false;
        $this->business = false;
        $this->registration = false;
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
     * Returns the id.
     *
     * @return int
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
    public function isBusiness()
    {
        return $this->business;
    }

    /**
     * @inheritdoc
     */
    public function setBusiness($business)
    {
        $this->business = (bool)$business;

        return $this;
    }

    /**
     * Returns the registration.
     *
     * @return bool
     */
    public function isRegistration()
    {
        return $this->registration;
    }

    /**
     * Sets the registration.
     *
     * @param bool $registration
     *
     * @return CustomerGroup
     */
    public function setRegistration($registration)
    {
        $this->registration = (bool)$registration;

        return $this;
    }


}
