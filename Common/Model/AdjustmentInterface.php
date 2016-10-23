<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Resource\Model as ResourceModel;

/**
 * Interface AdjustmentInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AdjustmentInterface extends ResourceModel\ResourceInterface, ResourceModel\SortableInterface
{
    /**
     * Returns the designation.
     *
     * @return string
     */
    public function getDesignation();

    /**
     * Sets the designation.
     *
     * @param string $designation
     * @return $this|AdjustmentInterface
     */
    public function setDesignation($designation);

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType();

    /**
     * Sets the type.
     *
     * @param string $type
     * @return $this|AdjustmentInterface
     */
    public function setType($type);

    /**
     * Returns the mode.
     *
     * @return string
     */
    public function getMode();

    /**
     * Sets the mode.
     *
     * @param string $mode
     * @return $this|AdjustmentInterface
     */
    public function setMode($mode);

    /**
     * Returns the amount.
     *
     * @return float
     */
    public function getAmount();

    /**
     * Sets the amount.
     *
     * @param float $amount
     * @return $this|AdjustmentInterface
     */
    public function setAmount($amount);

    /**
     * Returns the immutable.
     *
     * @return boolean
     */
    public function isImmutable();

    /**
     * Sets the immutable.
     *
     * @param boolean $immutable
     *
     * @return $this|AdjustmentInterface
     */
    public function setImmutable($immutable);

    /**
     * Returns whether this adjustment equals the given adjustment.
     *
     * @param AdjustmentInterface $adjustment
     *
     * @return bool
     */
    public function equals(AdjustmentInterface $adjustment);

    /**
     * Returns the adjustable.
     *
     * @return AdjustableInterface
     */
    public function getAdjustable();
}
