<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stat\Model;

use DateTimeInterface;
use Decimal\Decimal;

/**
 * Class AbstractStat
 * @package Ekyna\Component\Commerce\Stat\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StatInterface
{
    public const TYPE_YEAR  = 0;
    public const TYPE_MONTH = 1;
    public const TYPE_DAY   = 2;

    public function getId(): ?int;

    public function setId(?int $id): StatInterface;

    public function getType(): ?int;

    public function setType(?int $type): StatInterface;

    public function getDate(): ?string;

    public function setDate(?string $date): StatInterface;

    public function getRevenue(): Decimal;

    public function setRevenue(Decimal $revenue): StatInterface;

    public function getShipping(): Decimal;

    public function setShipping(Decimal $shipping): StatInterface;

    public function getCost(): Decimal;

    public function setCost(Decimal $cost): StatInterface;

    public function getCount(): int;

    public function setCount(int $count): self;

    public function getMargin(): Decimal;

    public function getUpdatedAt(): ?DateTimeInterface;

    public function setUpdatedAt(?DateTimeInterface $updatedAt): StatInterface;

    /**
     * Returns the margin in percentage.
     */
    public function getMarginPercent(): Decimal;

    /**
     * Loads the calculation result.
     *
     * @param array $result
     *
     * @return bool Whether a property has changed.
     */
    public function loadResult(array $result): bool;
}
