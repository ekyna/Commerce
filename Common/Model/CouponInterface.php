<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class Coupon
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CouponInterface extends ResourceInterface
{
    /**
     * Returns the code.
     *
     * @return string
     */
    public function getCode(): ?string;

    /**
     * Sets the code.
     *
     * @param string $code
     *
     * @return $this|CouponInterface
     */
    public function setCode(string $code): CouponInterface;

    /**
     * Returns the start date.
     *
     * @return \DateTime
     */
    public function getStartAt(): ?\DateTime;

    /**
     * Sets the start date.
     *
     * @param \DateTime $from
     *
     * @return $this|CouponInterface
     */
    public function setStartAt(\DateTime $from = null): CouponInterface;

    /**
     * Returns the end date.
     *
     * @return \DateTime
     */
    public function getEndAt(): ?\DateTime;

    /**
     * Sets the end date.
     *
     * @param \DateTime $to
     *
     * @return $this|CouponInterface
     */
    public function setEndAt(\DateTime $to = null): CouponInterface;

    /**
     * Returns the limit.
     *
     * @return int
     */
    public function getLimit(): int;

    /**
     * Sets the limit.
     *
     * @param int $limit
     *
     * @return $this|CouponInterface
     */
    public function setLimit(int $limit): CouponInterface;

    /**
     * Returns the usage.
     *
     * @return int
     */
    public function getUsage(): int;

    /**
     * Sets the usage.
     *
     * @param int $usage
     *
     * @return $this|CouponInterface
     */
    public function setUsage(int $usage): CouponInterface;

    /**
     * Returns the minimum gross amount.
     *
     * @return float
     */
    public function getMinGross(): float;

    /**
     * Sets the minimum gross amount.
     *
     * @param float $min
     *
     * @return $this|CouponInterface
     */
    public function setMinGross(float $min): CouponInterface;

    /**
     * Returns whether this coupon can be combined with other discounts.
     *
     * @return bool
     */
    public function isCumulative(): bool;

    /**
     * Sets whether this coupon can be combined with other discounts.
     *
     * @param bool $cumulative
     *
     * @return $this|CouponInterface
     */
    public function setCumulative(bool $cumulative): CouponInterface;

    /**
     * Returns the designation.
     *
     * @return string
     */
    public function getDesignation(): ?string;

    /**
     * Sets the designation.
     *
     * @param string $designation
     *
     * @return $this|CouponInterface
     */
    public function setDesignation(string $designation = null): CouponInterface;

    /**
     * Returns the mode.
     *
     * @return string
     */
    public function getMode(): ?string;

    /**
     * Sets the mode.
     *
     * @param string $mode
     *
     * @return $this|CouponInterface
     */
    public function setMode(string $mode): CouponInterface;

    /**
     * Returns the amount.
     *
     * @return float
     */
    public function getAmount(): ?float;

    /**
     * Sets the amount.
     *
     * @param float $amount
     *
     * @return $this|CouponInterface
     */
    public function setAmount(float $amount): CouponInterface;
}
