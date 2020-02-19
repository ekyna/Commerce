<?php

namespace Ekyna\Component\Commerce\Newsletter\Model;

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
     * Returns the audience.
     *
     * @return AudienceInterface
     */
    public function getAudience(): ?AudienceInterface;

    /**
     * Sets the audience.
     *
     * @param AudienceInterface $audience
     *
     * @return $this|MemberInterface
     */
    public function setAudience(AudienceInterface $audience): MemberInterface;

    /**
     * Returns the identifier.
     *
     * @return string
     */
    public function getIdentifier(): ?string;

    /**
     * Sets the identifier
     *
     * @param string $identifier
     *
     * @return Member
     */
    public function setIdentifier(string $identifier): MemberInterface;

    /**
     * Returns the customer.
     *
     * @return CustomerInterface
     */
    public function getCustomer(): ?CustomerInterface;

    /**
     * Sets the customer.
     *
     * @param CustomerInterface $customer
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
     * @return $this|MemberInterface
     */
    public function setAttributes(array $attributes): MemberInterface;
}
