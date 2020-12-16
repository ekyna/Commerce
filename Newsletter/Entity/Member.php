<?php

namespace Ekyna\Component\Commerce\Newsletter\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
use Ekyna\Component\Commerce\Newsletter\Model\SubscriptionInterface;
use Ekyna\Component\Commerce\Newsletter\Model\SubscriptionStatus;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class Member
 * @package Ekyna\Component\Commerce\Newsletter\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Member implements MemberInterface
{
    use TimestampableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string[]
     */
    protected $identifiers;

    /**
     * @var CustomerInterface
     */
    protected $customer;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var Collection|Subscription[]
     */
    protected $subscriptions;

    /**
     * @var string
     */
    protected $status;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->identifiers = [];
        $this->createdAt     = new DateTime();
        $this->subscriptions = new ArrayCollection();
        $this->status        = SubscriptionStatus::UNSUBSCRIBED;
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->email ?: 'New member';
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
    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    /**
     * @inheritDoc
     */
    public function hasIdentifier(string $gateway): bool
    {
        return isset($this->identifiers[$gateway]);
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(string $gateway): ?string
    {
        return $this->identifiers[$gateway] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function setIdentifier(string $gateway, string $identifier = null): MemberInterface
    {
        if (is_null($identifier)) {
            unset($this->identifiers[$gateway]);
        } else {
            $this->identifiers[$gateway] = $identifier;
        }

        return $this;
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
    public function setCustomer(CustomerInterface $customer = null): MemberInterface
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
    public function setEmail(string $email): MemberInterface
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasSubscription(SubscriptionInterface $subscription): bool
    {
        return $this->subscriptions->contains($subscription);
    }

    /**
     * @inheritDoc
     */
    public function addSubscription(SubscriptionInterface $subscription): MemberInterface
    {
        if (!$this->hasSubscription($subscription)) {
            $this->subscriptions->add($subscription);
            $subscription->setMember($this);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeSubscription(SubscriptionInterface $subscription): MemberInterface
    {
        if ($this->hasSubscription($subscription)) {
            $this->subscriptions->removeElement($subscription);
            $subscription->setMember(null);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSubscription(AudienceInterface $audience): ?SubscriptionInterface
    {
        foreach ($this->subscriptions as $subscription) {
            if ($subscription->getAudience() === $audience) {
                return $subscription;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @inheritDoc
     */
    public function setStatus(string $status): MemberInterface
    {
        $this->status = $status;

        return $this;
    }
}
