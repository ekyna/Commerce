<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface AdjustmentDataInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AdjustmentDataInterface
{
    /**
     * Returns the designation.
     *
     * @return string
     */
    public function getDesignation();

    /**
     * Returns the mode.
     *
     * @return string
     */
    public function getMode();

    /**
     * Returns the amount.
     *
     * @return float
     */
    public function getAmount();

    /**
     * Returns the source.
     *
     * @return string
     */
    public function getSource();

    /**
     * Returns the immutable.
     *
     * @return bool
     */
    public function isImmutable();
}
