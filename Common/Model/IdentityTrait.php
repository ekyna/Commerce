<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Trait IdentityTrait|$this
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait IdentityTrait
{
    protected ?string $gender    = null;
    protected ?string $firstName = null;
    protected ?string $lastName  = null;


    public function getGender(): ?string
    {
        return $this->gender;
    }

    /**
     * @return $this|IdentityInterface
     */
    public function setGender(?string $gender): IdentityInterface
    {
        $this->gender = $gender;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @return $this|IdentityInterface
     */
    public function setFirstName(?string $firstName): IdentityInterface
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @return $this|IdentityInterface
     */
    public function setLastName(?string $lastName): IdentityInterface
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Returns whether or not the identity is empty.
     */
    public function isIdentityEmpty(): bool
    {
        return empty($this->gender) && empty($this->firstName) && empty($this->lastName);
    }

    /**
     * Clears the identity.
     *
     * @return $this|IdentityInterface
     */
    public function clearIdentity(): IdentityInterface
    {
        $this->gender = null;
        $this->firstName = null;
        $this->lastName = null;

        return $this;
    }
}
