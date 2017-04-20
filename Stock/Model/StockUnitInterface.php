<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Model;

use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectInterface;
use Ekyna\Component\Commerce\Common\Model\StateSubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface StockUnitInterface
 * @package Ekyna\Component\Commerce\Stock\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockUnitInterface extends ResourceInterface, StateSubjectInterface, ExchangeSubjectInterface
{
    public function setSubject(?StockSubjectInterface $subject): StockUnitInterface;

    public function getSubject(): ?StockSubjectInterface;

    public function getWarehouse(): ?WarehouseInterface;

    public function setWarehouse(?WarehouseInterface $warehouse): StockUnitInterface;

    public function getSupplierOrderItem(): ?SupplierOrderItemInterface;

    public function setSupplierOrderItem(?SupplierOrderItemInterface $item): StockUnitInterface;

    public function getGeocodes(): array;

    public function hasGeocode(string $geocode): bool;

    public function addGeocode(string $geocode): StockUnitInterface;

    public function removeGeocode(string $geocode): StockUnitInterface;

    public function setGeocodes(array $codes): StockUnitInterface;

    public function getEstimatedDateOfArrival(): ?DateTimeInterface;

    public function setEstimatedDateOfArrival(?DateTimeInterface $date): StockUnitInterface;

    /**
     * Returns the net price (default currency).
     */
    public function getNetPrice(): Decimal;

    /**
     * Sets the net price (default currency).
     */
    public function setNetPrice(Decimal $price): StockUnitInterface;

    /**
     * Returns the shipping price (default currency).
     */
    public function getShippingPrice(): Decimal;

    /**
     * Sets the shipping price (default currency).
     */
    public function setShippingPrice(Decimal $price): StockUnitInterface;

    public function getOrderedQuantity(): Decimal;

    public function setOrderedQuantity(Decimal $quantity): StockUnitInterface;

    public function getReceivedQuantity(): Decimal;

    public function setReceivedQuantity(Decimal $quantity): StockUnitInterface;

    public function getAdjustedQuantity(): Decimal;

    public function setAdjustedQuantity(Decimal $quantity): StockUnitInterface;

    public function getSoldQuantity(): Decimal;

    public function setSoldQuantity(Decimal $quantity): StockUnitInterface;

    public function getShippedQuantity(): Decimal;

    public function setShippedQuantity(Decimal $quantity): StockUnitInterface;

    public function getLockedQuantity(): Decimal;

    public function setLockedQuantity(Decimal $quantity): StockUnitInterface;

    /**
     * Returns the 'created at' date.
     */
    public function getCreatedAt(): DateTimeInterface;

    /**
     * Sets the 'created at' date.
     */
    public function setCreatedAt(DateTimeInterface $date): StockUnitInterface;

    /**
     * Returns the 'closed at' date time.
     */
    public function getClosedAt(): ?DateTimeInterface;

    /**
     * Sets the 'closed at' at date time.
     */
    public function setClosedAt(?DateTimeInterface $date): StockUnitInterface;

    public function hasStockAssignment(StockAssignmentInterface $assignment): bool;

    public function addStockAssignment(StockAssignmentInterface $assignment): StockUnitInterface;

    public function removeStockAssignment(StockAssignmentInterface $assignment): StockUnitInterface;

    /**
     * @return Collection|StockAssignmentInterface[]
     */
    public function getStockAssignments(): Collection;

    public function hasStockAdjustment(StockAdjustmentInterface $adjustment): bool;

    public function addStockAdjustment(StockAdjustmentInterface $adjustment): StockUnitInterface;

    public function removeStockAdjustment(StockAdjustmentInterface $adjustment): StockUnitInterface;

    /**
     * @return Collection|StockAdjustmentInterface[]
     */
    public function getStockAdjustments(): Collection;

    /**
     * Returns whether this stock unit is empty (regarding to the ordered and sold quantities).
     */
    public function isEmpty(): bool;

    public function isClosed(): bool;

    public function getReservableQuantity(): Decimal;

    public function getReleasableQuantity(): Decimal;

    public function getShippableQuantity(): Decimal;

    public function getSupplierOrder(): ?SupplierOrderInterface;
}
