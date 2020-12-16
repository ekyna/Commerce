<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Class Coupon
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Coupon implements Model\CouponInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var CustomerInterface|null
     */
    private $customer;

    /**
     * @var string
     */
    private $code;

    /**
     * @var \DateTime
     */
    private $startAt;

    /**
     * @var \DateTime
     */
    private $endAt;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $usage;

    /**
     * @var float
     */
    private $minGross;

    /**
     * @var bool
     */
    private $cumulative;

    /**
     * @var string
     */
    private $designation;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var float
     */
    private $amount;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->limit = 0;
        $this->usage = 0;
        $this->minGross = 0;
        $this->cumulative = false;
        $this->mode = Model\AdjustmentModes::MODE_PERCENT;
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->code ?: 'New coupon';
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
    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    /**
     * @inheritDoc
     */
    public function setCustomer(CustomerInterface $customer = null): Model\CouponInterface
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @inheritDoc
     */
    public function setCode(string $code): Model\CouponInterface
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStartAt(): ?\DateTime
    {
        return $this->startAt;
    }

    /**
     * @inheritDoc
     */
    public function setStartAt(\DateTime $startAt = null): Model\CouponInterface
    {
        $this->startAt = $startAt;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getEndAt(): ?\DateTime
    {
        return $this->endAt;
    }

    /**
     * @inheritDoc
     */
    public function setEndAt(\DateTime $endAt = null): Model\CouponInterface
    {
        $this->endAt = $endAt;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @inheritDoc
     */
    public function setLimit(int $limit): Model\CouponInterface
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUsage(): int
    {
        return $this->usage;
    }

    /**
     * @inheritDoc
     */
    public function setUsage(int $usage): Model\CouponInterface
    {
        $this->usage = $usage;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMinGross(): float
    {
        return $this->minGross;
    }

    /**
     * @inheritDoc
     */
    public function setMinGross(float $min): Model\CouponInterface
    {
        $this->minGross = $min;

        return $this;
    }

    /**
     * Returns whether this coupon can be combined with other discounts.
     *
     * @return bool
     */
    public function isCumulative(): bool
    {
        return $this->cumulative;
    }

    /**
     * Sets whether this coupon can be combined with other discounts.
     *
     * @param bool $cumulative
     *
     * @return Coupon
     */
    public function setCumulative(bool $cumulative): Model\CouponInterface
    {
        $this->cumulative = $cumulative;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    /**
     * @inheritDoc
     */
    public function setDesignation(string $designation = null): Model\CouponInterface
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMode(): ?string
    {
        return $this->mode;
    }

    /**
     * @inheritDoc
     */
    public function setMode(string $mode = null): Model\CouponInterface
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @inheritDoc
     */
    public function setAmount(float $amount): Model\CouponInterface
    {
        $this->amount = $amount;

        return $this;
    }
}
