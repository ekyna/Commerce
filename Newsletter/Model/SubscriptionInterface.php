<?php

namespace Ekyna\Component\Commerce\Newsletter\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface SubscriptionInterface
 * @package Ekyna\Component\Commerce\Newsletter\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface SubscriptionInterface extends ResourceInterface
{
    /**
     * Returns the audience.
     *
     * @return AudienceInterface
     */
    public function getAudience(): ?AudienceInterface;

    /**
     * Sets the audience.
     *
     * @param AudienceInterface|null $audience
     *
     * @return $this|SubscriptionInterface
     */
    public function setAudience(AudienceInterface $audience = null): SubscriptionInterface;

    /**
     * Returns the member.
     *
     * @return MemberInterface
     */
    public function getMember(): ?MemberInterface;

    /**
     * Sets the member.
     *
     * @param MemberInterface|null $member
     *
     * @return $this|SubscriptionInterface
     */
    public function setMember(MemberInterface $member = null): SubscriptionInterface;

    /**
     * Returns the identifier.
     *
     * @return string
     */
    public function getIdentifier(): ?string;

    /**
     * Sets the identifier.
     *
     * @param string|null $identifier
     *
     * @return $this|SubscriptionInterface
     */
    public function setIdentifier(string $identifier = null): SubscriptionInterface;

    /**
     * Returns the attributes.
     *
     * @return array
     */
    public function getAttributes(): array;

    /**
     * Sets the attributes.
     *
     * @param array $attributes
     *
     * @return SubscriptionInterface
     */
    public function setAttributes(array $attributes): SubscriptionInterface;

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
     * @return $this|SubscriptionInterface
     */
    public function setStatus(string $status): SubscriptionInterface;
}
