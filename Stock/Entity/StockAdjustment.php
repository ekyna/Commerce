<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Entity;

use DateTime;
use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Stock\Model;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class StockAdjustment
 * @package Ekyna\Component\Commerce\Stock\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAdjustment extends AbstractResource implements Model\StockAdjustmentInterface
{
    protected ?Model\StockUnitInterface $stockUnit = null;
    protected Decimal                   $quantity;
    protected ?string                   $reason    = null;
    protected ?string                   $note      = null;
    protected DateTimeInterface         $createdAt;

    public function __construct()
    {
        $this->quantity = new Decimal(0);
        $this->createdAt = new DateTime();
    }

    public function getStockUnit(): ?Model\StockUnitInterface
    {
        return $this->stockUnit;
    }

    public function setStockUnit(?Model\StockUnitInterface $stockUnit): Model\StockAdjustmentInterface
    {
        if ($stockUnit === $this->stockUnit) {
            return $this;
        }

        if ($previous = $this->stockUnit) {
            $this->stockUnit = null;
            $previous->removeStockAdjustment($this);
        }

        if ($this->stockUnit = $stockUnit) {
            $this->stockUnit->addStockAdjustment($this);
        }

        return $this;
    }

    public function getQuantity(): Decimal
    {
        return $this->quantity;
    }

    public function setQuantity(Decimal $quantity): Model\StockAdjustmentInterface
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): Model\StockAdjustmentInterface
    {
        $this->reason = $reason;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(string $note): Model\StockAdjustmentInterface
    {
        $this->note = $note;

        return $this;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): Model\StockAdjustmentInterface
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
