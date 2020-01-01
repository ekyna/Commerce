<?php

namespace Ekyna\Component\Commerce\Customer\Entity;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Class LoyaltyLog
 * @package Ekyna\Component\Commerce\Customer\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LoyaltyLog
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var CustomerInterface
     */
    private $customer;

    /**
     * @var bool
     */
    private $debit = false;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var string|null
     */
    private $origin;

    /**
     * @var \DateTime
     */
    private $createdAt;


    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns the customer.
     *
     * @return CustomerInterface
     */
    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    /**
     * Sets the customer.
     *
     * @param CustomerInterface $customer
     *
     * @return LoyaltyLog
     */
    public function setCustomer(CustomerInterface $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Returns whether this log represents a debit.
     *
     * @return bool
     */
    public function isDebit(): bool
    {
        return $this->debit;
    }

    /**
     * Sets whether this log represents a debit.
     *
     * @param bool $debit
     *
     * @return LoyaltyLog
     */
    public function setDebit(bool $debit): self
    {
        $this->debit = $debit;

        return $this;
    }

    /**
     * Returns the amount of points.
     *
     * @return int
     */
    public function getAmount(): ?int
    {
        return $this->amount;
    }

    /**
     * Sets the amount of points.
     *
     * @param int $amount
     *
     * @return LoyaltyLog
     */
    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Returns the origin of this log.
     *
     * @return string
     */
    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    /**
     * Sets the the origin of this log.
     *
     * @param string $origin
     *
     * @return LoyaltyLog
     */
    public function setOrigin(string $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * Returns the "created at" date.
     *
     * @return \DateTime
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * Sets the "created at" date.
     *
     * @param \DateTime $createdAt
     *
     * @return LoyaltyLog
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
