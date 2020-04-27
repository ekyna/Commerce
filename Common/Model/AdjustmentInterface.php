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
    public function getDesignation(): ?string;

    /**
     * Sets the designation.
     *
     * @param string $designation
     *
     * @return $this|AdjustmentInterface
     */
    public function setDesignation(string $designation = null): AdjustmentInterface;

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return $this|AdjustmentInterface
     */
    public function setType(string $type): AdjustmentInterface;

    /**
     * Returns the mode.
     *
     * @return string
     */
    public function getMode(): string;

    /**
     * Sets the mode.
     *
     * @param string $mode
     *
     * @return $this|AdjustmentInterface
     */
    public function setMode(string $mode): AdjustmentInterface;

    /**
     * Returns the amount.
     *
     * @return float
     */
    public function getAmount(): ?float;

    /**
     * Sets the amount.
     *
     * @param float $amount
     *
     * @return $this|AdjustmentInterface
     */
    public function setAmount(float $amount): AdjustmentInterface;

    /**
     * Returns the immutable.
     *
     * @return bool
     */
    public function isImmutable(): bool;

    /**
     * Sets the immutable.
     *
     * @param bool $immutable
     *
     * @return $this|AdjustmentInterface
     */
    public function setImmutable(bool $immutable): AdjustmentInterface;

    /**
     * Returns the source.
     *
     * @return string
     */
    public function getSource(): ?string;

    /**
     * Sets the source.
     *
     * @param string $source
     *
     * @return AdjustmentInterface
     */
    public function setSource(string $source = null): AdjustmentInterface;

    /**
     * Returns whether this adjustment equals the given adjustment.
     *
     * @param AdjustmentInterface $adjustment
     *
     * @return bool
     */
    public function equals(AdjustmentInterface $adjustment): bool;

    /**
     * Returns the adjustable.
     *
     * @return AdjustableInterface
     */
    public function getAdjustable(): ?AdjustableInterface;
}
