<?php

namespace Ekyna\Component\Commerce\Stock\Entity;

use DateTime;
use Ekyna\Component\Commerce\Stock\Model;

/**
 * Class StockAdjustment
 * @package Ekyna\Component\Commerce\Stock\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAdjustment implements Model\StockAdjustmentInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\StockUnitInterface
     */
    protected $stockUnit;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * @var string
     */
    protected $reason;

    /**
     * @var string
     */
    protected $note;

    /**
     * @var DateTime
     */
    protected $createdAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->quantity = 0.;
        $this->createdAt = new DateTime();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getStockUnit(): ?Model\StockUnitInterface
    {
        return $this->stockUnit;
    }

    /**
     * @inheritdoc
     */
    public function setStockUnit(Model\StockUnitInterface $stockUnit = null): Model\StockAdjustmentInterface
    {
        if ($this->stockUnit === $stockUnit) {
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

    /**
     * @inheritdoc
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }

    /**
     * @inheritdoc
     */
    public function setQuantity(float $quantity): Model\StockAdjustmentInterface
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @inheritdoc
     */
    public function setReason(string $reason): Model\StockAdjustmentInterface
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getNote(): ?string
    {
        return $this->note;
    }

    /**
     * @inheritDoc
     */
    public function setNote(string $note): Model\StockAdjustmentInterface
    {
        $this->note = $note;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(DateTime $createdAt): Model\StockAdjustmentInterface
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
