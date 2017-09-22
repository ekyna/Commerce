<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Trait IdentityTrait|$this
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait IdentityTrait
{
    /**
     * @var string
     */
    protected $gender;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;


    /**
     * Returns the gender.
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Sets the gender.
     *
     * @param string $gender
     *
     * @return IdentityInterface|$this
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Returns the firstName.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Sets the firstName.
     *
     * @param string $firstName
     *
     * @return IdentityInterface|$this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Returns the lastName.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Sets the lastName.
     *
     * @param string $lastName
     *
     * @return IdentityInterface|$this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Returns whether or not the identity is empty.
     *
     * @return bool
     */
    public function isIdentityEmpty()
    {
        return empty($this->gender) && empty($this->firstName) && empty($this->lastName);
    }
}
