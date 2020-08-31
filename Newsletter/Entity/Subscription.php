<?php

namespace Ekyna\Component\Commerce\Newsletter\Entity;

use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
use Ekyna\Component\Commerce\Newsletter\Model\SubscriptionInterface;
use Ekyna\Component\Commerce\Newsletter\Model\SubscriptionStatus;

/**
 * Class Subscription
 * @package Ekyna\Component\Commerce\Newsletter\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Subscription implements SubscriptionInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var AudienceInterface
     */
    protected $audience;

    /**
     * @var MemberInterface
     */
    protected $member;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var string
     */
    protected $status;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->attributes = [];
        $this->status     = SubscriptionStatus::UNSUBSCRIBED;
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAudience(): ?AudienceInterface
    {
        return $this->audience;
    }

    /**
     * @inheritdoc
     */
    public function setAudience(AudienceInterface $audience = null): SubscriptionInterface
    {
        $this->audience = $audience;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMember(): ?MemberInterface
    {
        return $this->member;
    }

    /**
     * @inheritdoc
     */
    public function setMember(MemberInterface $member = null): SubscriptionInterface
    {
        if ($member !== $this->member) {
            if ($previous = $this->member) {
                $this->member = null;
                $previous->removeSubscription($this);
            }

            if ($this->member = $member) {
                $this->member->addSubscription($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * @inheritdoc
     */
    public function setIdentifier(string $identifier = null): SubscriptionInterface
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @inheritDoc
     */
    public function setAttributes(array $attributes): SubscriptionInterface
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @inheritdoc
     */
    public function setStatus(string $status): SubscriptionInterface
    {
        $this->status = $status;

        return $this;
    }
}
