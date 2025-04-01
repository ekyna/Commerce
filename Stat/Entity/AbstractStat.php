<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stat\Entity;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Stat\Model\StatInterface;

/**
 * Class AbstractStat
 * @package Ekyna\Component\Commerce\Stat\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AbstractStat implements StatInterface
{
    protected ?int               $id        = null;
    protected ?int               $type      = null;
    protected ?string            $date      = null;
    protected Decimal            $revenue;
    protected Decimal            $shipping;
    protected Decimal            $cost;
    protected int                $count    = 0;
    protected ?DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->revenue = new Decimal(0);
        $this->shipping = new Decimal(0);
        $this->cost = new Decimal(0);
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

    public function getCost(): Decimal
    {
        return $this->cost;
    }

    public function setCost(Decimal $cost): self
    {
        $this->cost = $cost;

        return $this;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }

    public function getMargin(): Decimal
    {
        return $this->revenue->add($this->shipping)->sub($this->cost);
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
        if (0 < $revenue = $this->revenue->add($this->shipping)) {
            // (1 - (cost / revenue) ) * 100
            return (new Decimal(1))->sub(
                $this->cost->div($revenue)
            )->mul(100)->round(2);
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

        foreach ($this->getMap() as $property => $type) {
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

    protected function getMap(): array
    {
        return [
            'revenue'  => 'decimal',
            'shipping' => 'decimal',
            'cost'     => 'decimal',
        ];
    }
}
