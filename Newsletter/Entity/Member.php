<?php

namespace Ekyna\Component\Commerce\Newsletter\Entity;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
use Ekyna\Component\Commerce\Newsletter\Model\MemberStatuses;
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
     * @var AudienceInterface
     */
    protected $audience;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var CustomerInterface
     */
    protected $customer;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var array
     */
    protected $attributes;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->status = MemberStatuses::UNSUBSCRIBED;
        $this->attributes = [];
        $this->createdAt = new \DateTime();
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->email;
    }

    /**
     * @inheritDoc
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getAudience(): ?AudienceInterface
    {
        return $this->audience;
    }

    /**
     * @inheritDoc
     */
    public function setAudience(AudienceInterface $audience): MemberInterface
    {
        $this->audience = $audience;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * @inheritDoc
     */
    public function setIdentifier(string $identifier): MemberInterface
    {
        $this->identifier = $identifier;

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
    public function setAttributes(array $attributes): MemberInterface
    {
        $this->attributes = $attributes;

        return $this;
    }
}
