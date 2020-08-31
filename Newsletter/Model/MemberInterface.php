<?php

namespace Ekyna\Component\Commerce\Newsletter\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Newsletter\Entity\Member;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;

/**
 * Interface MemberInterface
 * @package Ekyna\Component\Commerce\Newsletter\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface MemberInterface extends ResourceInterface, TimestampableInterface
{
    /**
     * Returns the identifiers.
     *
     * @return string[]
     */
    public function getIdentifiers(): array;

    /**
     * Returns whether a identifier is set for the given gateway.
     *
     * @param string $gateway
     *
     * @return bool
     */
    public function hasIdentifier(string $gateway): bool;

    /**
     * Returns the identifier for the given gateway.
     *
     * @param string $gateway
     *
     * @return string|null
     */
    public function getIdentifier(string $gateway): ?string;

    /**
     * Sets the identifier for the given gateway
     *
     * @param string      $gateway
     * @param string|null $identifier
     *
     * @return Member
     */
    public function setIdentifier(string $gateway, string $identifier = null): MemberInterface;

    /**
     * Returns the customer.
     *
     * @return CustomerInterface
     */
    public function getCustomer(): ?CustomerInterface;

    /**
     * Sets the customer.
     *
     * @param CustomerInterface|null $customer
     *
     * @return $this|MemberInterface
     */
    public function setCustomer(CustomerInterface $customer = null): MemberInterface;

    /**
     * Returns the email.
     *
     * @return string
     */
    public function getEmail(): ?string;

    /**
     * Sets the email.
     *
     * @param string $email
     *
     * @return $this|MemberInterface
     */
    public function setEmail(string $email): MemberInterface;

    /**
     * Returns whether the member has the given subscription.
     *
     * @param SubscriptionInterface $subscription
     *
     * @return bool
     */
    public function hasSubscription(SubscriptionInterface $subscription): bool;

    /**
     * Adds the subscription.
     *
     * @param SubscriptionInterface $subscription
     *
     * @return $this|MemberInterface
     */
    public function addSubscription(SubscriptionInterface $subscription): MemberInterface;

    /**
     * Removes the subscription.
     *
     * @param SubscriptionInterface $subscription
     *
     * @return $this|MemberInterface
     */
    public function removeSubscription(SubscriptionInterface $subscription): MemberInterface;

    /**
     * Returns the subscription for the given audience.
     *
     * @param AudienceInterface $audience
     *
     * @return SubscriptionInterface|null
     */
    public function getSubscription(AudienceInterface $audience): ?SubscriptionInterface;

    /**
     * Returns the subscriptions.
     *
     * @return Collection|SubscriptionInterface[]
     */
    public function getSubscriptions(): Collection;

    /**
     * Returns the status.
     *
     * @return string
     */
    public function getStatus(): string;

    /**
     * Sets the status.
     *
     * @param string $status
     *
     * @return $this|MemberInterface
     */
    public function setStatus(string $status): MemberInterface;
}
