<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Model;

use Decimal\Decimal;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface AssignmentInterface
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AssignmentInterface extends ResourceInterface
{
    public function getStockUnit(): ?StockUnitInterface;

    /**
     * @return $this|AssignmentInterface
     */
    public function setStockUnit(?StockUnitInterface $stockUnit): AssignmentInterface;

    public function getAssignable(): ?AssignableInterface;

    public function setAssignable(?AssignableInterface $assignable): AssignmentInterface;

    public function getSoldQuantity(): Decimal;

    /**
     * @return $this|AssignmentInterface
     */
    public function setSoldQuantity(Decimal $quantity): AssignmentInterface;

    public function getShippedQuantity(): Decimal;

    /**
     * @return $this|AssignmentInterface
     */
    public function setShippedQuantity(Decimal $quantity): AssignmentInterface;

    public function getLockedQuantity(): Decimal;

    /**
     * @return $this|AssignmentInterface
     */
    public function setLockedQuantity(Decimal $quantity): AssignmentInterface;

    public function getShippableQuantity(): Decimal;

    public function getReleasableQuantity(): Decimal;

    /**
     * Returns whether the assignment is fully shipped.
     */
    public function isFullyShipped(): bool;

    /**
     * Returns whether the assignment is fully shippable.
     */
    public function isFullyShippable(): bool;

    /**
     * Returns whether the assignment is empty.
     */
    public function isEmpty(): bool;

    /**
     * Returns whether this assignment can be removed.
     *
     * @return bool
     */
    public function isRemovalPrevented(): bool;
}
