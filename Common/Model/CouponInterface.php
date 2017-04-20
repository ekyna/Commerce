<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class Coupon
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CouponInterface extends ResourceInterface
{
    /**
     * Returns the customer that owns this coupon.
     */
    public function getCustomer(): ?CustomerInterface;

    /**
     * Sets the customer that owns this coupon.
     */
    public function setCustomer(?CustomerInterface $customer): CouponInterface;

    public function getCode(): ?string;

    public function setCode(string $code): CouponInterface;

    public function getStartAt(): ?DateTimeInterface;

    public function setStartAt(DateTimeInterface $date): CouponInterface;

    public function getEndAt(): ?DateTimeInterface;

    public function setEndAt(?DateTimeInterface $date): CouponInterface;

    public function getLimit(): int;

    public function setLimit(int $limit): CouponInterface;

    public function getUsage(): int;

    public function setUsage(int $usage): CouponInterface;

    public function getMinGross(): Decimal;

    public function setMinGross(Decimal $min): CouponInterface;

    /**
     * Returns whether this coupon can be combined with other discounts.
     */
    public function isCumulative(): bool;

    /**
     * Sets whether this coupon can be combined with other discounts.
     */
    public function setCumulative(bool $cumulative): CouponInterface;

    public function getDesignation(): ?string;

    public function setDesignation(?string $designation): CouponInterface;

    public function getMode(): string;

    public function setMode(string $mode): CouponInterface;

    public function getAmount(): Decimal;

    public function setAmount(Decimal $amount): CouponInterface;
}
