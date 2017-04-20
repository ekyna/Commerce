<?php

declare(strict_types=1);

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
    use IdentityTrait;
    use NotificationsTrait;
    use TimestampableTrait;

    private ?int               $id          = null;
    private ?CustomerInterface $customer    = null;
    private ?string            $email       = null;
    private ?string            $title       = null;
    private ?PhoneNumber       $phone       = null;
    private ?string            $description = null;

    public function __toString(): string
    {
        return $this->email ?: 'New customer contact';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerInterface $customer): CustomerContactInterface
    {
        $this->customer = $customer;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): CustomerContactInterface
    {
        $this->email = $email;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): CustomerContactInterface
    {
        $this->title = $title;

        return $this;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function setPhone(?PhoneNumber $phone): CustomerContactInterface
    {
        $this->phone = $phone;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): CustomerContactInterface
    {
        $this->description = $description;

        return $this;
    }
}
