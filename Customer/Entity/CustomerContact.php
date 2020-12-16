<?php

namespace Ekyna\Component\Commerce\Customer\Entity;

use Ekyna\Component\Commerce\Common\Model\IdentityTrait;
use Ekyna\Component\Commerce\Customer\Model\CustomerContactInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Model\NotificationsTrait;
use Ekyna\Component\Resource\Model\TimestampableTrait;
use libphonenumber\PhoneNumber;

/**
 * Class CustomerContact
 * @package Ekyna\Component\Commerce\Customer\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerContact implements CustomerContactInterface
{
    use IdentityTrait,
        NotificationsTrait,
        TimestampableTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var CustomerInterface
     */
    private $customer;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $title;

    /**
     * @var PhoneNumber
     */
    private $phone;

    /**
     * @var string
     */
    private $description;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->notifications = [];
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->email ?: 'New customer contact';
    }

    /**
     * @inheritDoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    /**
     * @inheritDoc
     */
    public function setCustomer(CustomerInterface $customer = null): CustomerContactInterface
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @inheritDoc
     */
    public function setEmail(string $email): CustomerContactInterface
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function setTitle(string $title = null): CustomerContactInterface
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    /**
     * @inheritDoc
     */
    public function setPhone(PhoneNumber $phone = null): CustomerContactInterface
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @inheritDoc
     */
    public function setDescription(string $description = null): CustomerContactInterface
    {
        $this->description = $description;

        return $this;
    }
}
