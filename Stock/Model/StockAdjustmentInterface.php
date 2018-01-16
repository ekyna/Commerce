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
     * @return StockUnitInterface
     */
    public function getStockUnit();

    /**
     * Sets the stock unit.
     *
     * @param StockUnitInterface $stockUnit
     *
     * @return $this|StockAdjustmentInterface
     */
    public function setStockUnit(StockUnitInterface $stockUnit = null);

    /**
     * Returns the quantity.
     *
     * @return float
     */
    public function getQuantity();

    /**
     * Sets the quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockAdjustmentInterface
     */
    public function setQuantity($quantity);

    /**
     * Returns the reason.
     *
     * @return string
     */
    public function getReason();

    /**
     * Sets the reason.
     *
     * @param string $reason
     *
     * @return $this|StockAdjustmentInterface
     */
    public function setReason($reason);

    /**
     * Returns the note.
     *
     * @return string
     */
    public function getNote();

    /**
     * Sets the note.
     *
     * @param string $note
     *
     * @return $this|StockAdjustmentInterface
     */
    public function setNote($note);

    /**
     * Returns the "created at" date time.
     *
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * Sets the "created at" date time.
     *
     * @param \DateTime $createdAt
     *
     * @return $this|StockAdjustmentInterface
     */
    public function setCreatedAt(\DateTime $createdAt);
}