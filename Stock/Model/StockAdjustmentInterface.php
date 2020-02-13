<?php

namespace Ekyna\Component\Commerce\Stock\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface StockUnitAdjustmentInterface
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockAdjustmentInterface extends ResourceInterface
{
    /**
     * Returns the stock unit.
     *
     * @return StockUnitInterface|null
     */
    public function getStockUnit(): ?StockUnitInterface;

    /**
     * Sets the stock unit.
     *
     * @param StockUnitInterface $stockUnit
     *
     * @return $this|StockAdjustmentInterface
     */
    public function setStockUnit(StockUnitInterface $stockUnit = null): StockAdjustmentInterface;

    /**
     * Returns the quantity.
     *
     * @return float
     */
    public function getQuantity(): float;

    /**
     * Sets the quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockAdjustmentInterface
     */
    public function setQuantity(float $quantity): StockAdjustmentInterface;

    /**
     * Returns the reason.
     *
     * @return string
     */
    public function getReason(): ?string;

    /**
     * Sets the reason.
     *
     * @param string $reason
     *
     * @return $this|StockAdjustmentInterface
     */
    public function setReason(string $reason): StockAdjustmentInterface;

    /**
     * Returns the note.
     *
     * @return string
     */
    public function getNote(): ?string;

    /**
     * Sets the note.
     *
     * @param string $note
     *
     * @return $this|StockAdjustmentInterface
     */
    public function setNote(string $note): StockAdjustmentInterface;

    /**
     * Returns the "created at" date time.
     *
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime;

    /**
     * Sets the "created at" date time.
     *
     * @param \DateTime $createdAt
     *
     * @return $this|StockAdjustmentInterface
     */
    public function setCreatedAt(\DateTime $createdAt): StockAdjustmentInterface;
}
