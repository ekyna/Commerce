<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stat\Entity;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Exception\RuntimeException;

/**
 * Class OrderStat
 * @package Ekyna\Component\Commerce\Stat\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderStat
{
    public const TYPE_YEAR  = 0;
    public const TYPE_MONTH = 1;
    public const TYPE_DAY   = 2;

    private ?int               $id        = null;
    private ?int               $type      = null;
    private ?string            $date      = null;
    private Decimal            $revenue;
    private Decimal            $shipping;
    private Decimal            $margin;
    private int                $orders    = 0;
    private int                $items     = 0;
    private Decimal            $average;
    private array              $details   = [];
    private ?DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->revenue = new Decimal(0);
        $this->shipping = new Decimal(0);
        $this->margin = new Decimal(0);
        $this->average = new Decimal(0);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getRevenue(): Decimal
    {
        return $this->revenue;
    }

    public function setRevenue(Decimal $revenue): self
    {
        $this->revenue = $revenue;

        return $this;
    }

    public function getShipping(): Decimal
    {
        return $this->shipping;
    }

    public function setShipping(Decimal $shipping): self
    {
        $this->shipping = $shipping;

        return $this;
    }

    public function getMargin(): Decimal
    {
        return $this->margin;
    }

    public function setMargin(Decimal $margin): self
    {
        $this->margin = $margin;

        return $this;
    }

    /**
     * Returns the orders count.
     */
    public function getOrders(): int
    {
        return $this->orders;
    }

    /**
     * Sets the orders count.
     */
    public function setOrders(int $orders): self
    {
        $this->orders = $orders;

        return $this;
    }

    /**
     * Returns the items count.
     */
    public function getItems(): int
    {
        return $this->items;
    }

    /**
     * Sets the items count.
     */
    public function setItems(int $items): self
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Returns the average total.
     */
    public function getAverage(): Decimal
    {
        return $this->average;
    }

    /**
     * Sets the average total.
     */
    public function setAverage(Decimal $average): self
    {
        $this->average = $average;

        return $this;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function setDetails(array $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Returns the margin in percentage.
     */
    public function getMarginPercent(): Decimal
    {
        if (0 < $this->margin && 0 < $this->revenue) {
            return $this->margin->mul(100)->div($this->revenue)->round(1);
        }

        return new Decimal(0);
    }

    /**
     * Loads the calculation result.
     *
     * @param array $result
     *
     * @return bool Whether a property has changed.
     */
    public function loadResult(array $result): bool
    {
        $changed = false;

        $map = [
            'revenue'  => 'decimal',
            'shipping' => 'decimal',
            'margin'   => 'decimal',
            'orders'   => 'int',
            'items'    => 'int',
            'average'  => 'decimal',
            'details'  => 'array',
        ];

        foreach ($map as $property => $type) {
            if (!isset($result[$property])) {
                continue;
            }

            if ($type === 'decimal') {
                $value = new Decimal($result[$property]);

                if (!$value->equals($this->{$property})) {
                    $this->{$property} = $value;
                    $changed = true;
                }

                continue;
            }

            if ($type === 'int') {
                $value = (int)$result[$property];
            } elseif ($type === 'array') {
                $value = (array)$result[$property];
            } else {
                throw new RuntimeException();
            }

            if ($value !== $this->{$property}) {
                $this->{$property} = $value;
                $changed = true;
            }
        }

        return $changed;
    }
}
