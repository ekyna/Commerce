<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface StockAssignmentInterface
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockAssignmentInterface extends ResourceInterface
{
    public function getStockUnit(): ?StockUnitInterface;

    /**
     * @return $this|StockAssignmentInterface
     */
    public function setStockUnit(?StockUnitInterface $stockUnit): StockAssignmentInterface;

    /**
     * Returns the sale item.
     */
    public function getSaleItem(): ?SaleItemInterface;

    /**
     * @return $this|StockAssignmentInterface
     */
    public function setSaleItem(?SaleItemInterface $saleItem): StockAssignmentInterface;

    public function getSoldQuantity(): Decimal;

    /**
     * @return $this|StockAssignmentInterface
     */
    public function setSoldQuantity(Decimal $quantity): StockAssignmentInterface;

    public function getShippedQuantity(): Decimal;

    /**
     * @return $this|StockAssignmentInterface
     */
    public function setShippedQuantity(Decimal $quantity): StockAssignmentInterface;

    public function getLockedQuantity(): Decimal;

    /**
     * @return $this|StockAssignmentInterface
     */
    public function setLockedQuantity(Decimal $quantity): StockAssignmentInterface;

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
}
