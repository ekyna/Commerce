<?php

namespace Ekyna\Component\Commerce\Newsletter\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use libphonenumber\PhoneNumber;

/**
 * Class NewsletterSubscription
 * @package Ekyna\Component\Commerce\Newsletter\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NewsletterSubscription
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
     * @var PhoneNumber
     *
     * @TODO
     */
    //private $mobile;

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
     * @return NewsletterSubscription
     */
    public function setEmail(string $email): NewsletterSubscription
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
     * @return NewsletterSubscription
     */
    public function setFirstName(?string $firstName): NewsletterSubscription
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
     * @return NewsletterSubscription
     */
    public function setLastName(?string $lastName): NewsletterSubscription
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
     * @return NewsletterSubscription
     */
    public function setBirthday(?\DateTime $birthday): NewsletterSubscription
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
     * @return NewsletterSubscription
     */
    public function addAudience(AudienceInterface $audience): NewsletterSubscription
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
     * @return NewsletterSubscription
     */
    public function removeAudience(AudienceInterface $audience): NewsletterSubscription
    {
        if ($this->audiences->contains($audience)) {
            $this->audiences->removeElement($audience);
        }

        return $this;
    }
}
