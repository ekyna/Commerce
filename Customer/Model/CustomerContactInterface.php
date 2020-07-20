<?php

namespace Ekyna\Component\Commerce\Customer\Model;

use Ekyna\Component\Commerce\Common\Model\IdentityInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;
use libphonenumber\PhoneNumber;

/**
 * Interface CustomerContactInterface
 * @package Ekyna\Component\Commerce\Customer\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerContactInterface
    extends ResourceInterface, IdentityInterface, NotificationsInterface, TimestampableInterface
{
    /**
     * Returns the customer.
     *
     * @return CustomerInterface|null
     */
    public function getCustomer(): ?CustomerInterface;

    /**
     * Sets the customer.
     *
     * @param CustomerInterface|null $customer
     *
     * @return CustomerContactInterface
     */
    public function setCustomer(CustomerInterface $customer = null): CustomerContactInterface;

    /**
     * Returns the email.
     *
     * @return string|null
     */
    public function getEmail(): ?string;

    /**
     * Sets the email.
     *
     * @param string $email
     *
     * @return CustomerContactInterface
     */
    public function setEmail(string $email): CustomerContactInterface;

    /**
     * Returns the title.
     *
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * Sets the title.
     *
     * @param string $title
     *
     * @return CustomerContactInterface
     */
    public function setTitle(string $title = null): CustomerContactInterface;

    /**
     * Returns the phone.
     *
     * @return PhoneNumber|null
     */
    public function getPhone(): ?PhoneNumber;

    /**
     * Sets the phone.
     *
     * @param PhoneNumber $phone
     *
     * @return CustomerContactInterface
     */
    public function setPhone(PhoneNumber $phone = null): CustomerContactInterface;

    /**
     * Returns the description.
     *
     * @return string
     */
    public function getDescription(): ?string;

    /**
     * Sets the description.
     *
     * @param string $description
     *
     * @return CustomerContactInterface
     */
    public function setDescription(string $description = null): CustomerContactInterface;
}
