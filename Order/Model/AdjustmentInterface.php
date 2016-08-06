<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Common\Model\EntityInterface;

/**
 * Interface AdjustmentInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AdjustmentInterface extends EntityInterface
{
    const MODE_FLAT    = 'flat';
    const MODE_PERCENT = 'percent';

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
     * Returns the position.
     *
     * @return int
     */
    public function getPosition();

    /**
     * Sets the position.
     *
     * @param int $position
     * @return $this|AdjustmentInterface
     */
    public function setPosition($position);
}
