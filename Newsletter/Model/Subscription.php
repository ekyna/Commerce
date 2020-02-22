<?php

namespace Ekyna\Component\Commerce\Newsletter\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class Subscription
 * @package Ekyna\Component\Commerce\Newsletter\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Subscription
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string|null
     */
    private $firstName;

    /**
     * @var string|null
     */
    private $lastName;

    /**
     * @var \DateTime|null
     */
    private $birthday;

    /**
     * @var Collection|AudienceInterface[]
     */
    private $audiences;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->audiences = new ArrayCollection();
    }

    /**
     * Returns the email.
     *
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Sets the email.
     *
     * @param string $email
     *
     * @return Subscription
     */
    public function setEmail(string $email): Subscription
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Returns the firstName.
     *
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * Sets the firstName.
     *
     * @param string|null $firstName
     *
     * @return Subscription
     */
    public function setFirstName(?string $firstName): Subscription
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Returns the lastName.
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * Sets the lastName.
     *
     * @param string|null $lastName
     *
     * @return Subscription
     */
    public function setLastName(?string $lastName): Subscription
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Returns the birthday.
     *
     * @return \DateTime|null
     */
    public function getBirthday(): ?\DateTime
    {
        return $this->birthday;
    }

    /**
     * Sets the birthday.
     *
     * @param \DateTime|null $birthday
     *
     * @return Subscription
     */
    public function setBirthday(?\DateTime $birthday): Subscription
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Returns the audiences.
     *
     * @return Collection|AudienceInterface[]
     */
    public function getAudiences(): Collection
    {
        return $this->audiences;
    }

    /**
     * Adds the audiences.
     *
     * @param AudienceInterface $audience
     *
     * @return Subscription
     */
    public function addAudience(AudienceInterface $audience): Subscription
    {
        if (!$this->audiences->contains($audience)) {
            $this->audiences->add($audience);
        }

        return $this;
    }

    /**
     * Removes the audiences.
     *
     * @param AudienceInterface $audience
     *
     * @return Subscription
     */
    public function removeAudience(AudienceInterface $audience): Subscription
    {
        if ($this->audiences->contains($audience)) {
            $this->audiences->removeElement($audience);
        }

        return $this;
    }
}
