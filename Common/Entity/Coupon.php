<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class Coupon
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Coupon extends AbstractResource implements Model\CouponInterface
{
    private ?CustomerInterface $customer    = null;
    private ?string            $code        = null;
    private ?DateTimeInterface $startAt     = null;
    private ?DateTimeInterface $endAt       = null;
    private int                $limit       = 0;
    private int                $usage       = 0;
    private Decimal            $minGross;
    private bool               $cumulative  = false;
    private ?string            $designation = null;
    private string             $mode        = Model\AdjustmentModes::MODE_PERCENT;
    private Decimal            $amount;

    public function __construct()
    {
        $this->minGross = new Decimal(0);
        $this->amount = new Decimal(0);
    }

    public function __toString(): string
    {
        return $this->code ?: 'New coupon';
    }

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerInterface $customer): Model\CouponInterface
    {
        $this->customer = $customer;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): Model\CouponInterface
    {
        $this->code = $code;

        return $this;
    }

    public function getStartAt(): ?DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(?DateTimeInterface $date): Model\CouponInterface
    {
        $this->startAt = $date;

        return $this;
    }

    public function getEndAt(): ?DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(?DateTimeInterface $date): Model\CouponInterface
    {
        $this->endAt = $date;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): Model\CouponInterface
    {
        $this->limit = $limit;

        return $this;
    }

    public function getUsage(): int
    {
        return $this->usage;
    }

    public function setUsage(int $usage): Model\CouponInterface
    {
        $this->usage = $usage;

        return $this;
    }

    public function getMinGross(): Decimal
    {
        return $this->minGross;
    }

    public function setMinGross(Decimal $min): Model\CouponInterface
    {
        $this->minGross = $min;

        return $this;
    }

    public function isCumulative(): bool
    {
        return $this->cumulative;
    }

    public function setCumulative(bool $cumulative): Model\CouponInterface
    {
        $this->cumulative = $cumulative;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): Model\CouponInterface
    {
        $this->designation = $designation;

        return $this;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): Model\CouponInterface
    {
        $this->mode = $mode;

        return $this;
    }

    public function getAmount(): Decimal
    {
        return $this->amount;
    }

    public function setAmount(Decimal $amount): Model\CouponInterface
    {
        $this->amount = $amount;

        return $this;
    }
}
