<?php

namespace Ekyna\Component\Commerce\Customer\Model;

use Ekyna\Component\Commerce\Common\Model\IdentityInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;

/**
 * Interface CustomerContactInterface
 * @package Ekyna\Component\Commerce\Customer\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerContactInterface extends ResourceInterface, IdentityInterface, TimestampableInterface
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
     * Returns the phone.
     *
     * @return string|null
     */
    public function getPhone(): ?string;

    /**
     * Sets the phone.
     *
     * @param string $phone
     *
     * @return CustomerContactInterface
     */
    public function setPhone(string $phone): CustomerContactInterface;

    /**
     * Returns the notifications.
     *
     * @return string[]
     */
    public function getNotifications(): array;

    /**
     * Sets the notifications.
     *
     * @param string[] $notifications
     *
     * @return CustomerContactInterface
     */
    public function setNotifications(array $notifications = []): CustomerContactInterface;

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
