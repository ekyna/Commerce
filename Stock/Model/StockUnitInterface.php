<?php

namespace Ekyna\Component\Commerce\Stock\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\StateSubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface StockUnitInterface
 * @package Ekyna\Component\Commerce\Stock\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockUnitInterface extends ResourceInterface, StateSubjectInterface
{
    /**
     * Sets the subject.
     *
     * @param StockSubjectInterface $subject
     *
     * @return $this|StockUnitInterface
     */
    public function setSubject(StockSubjectInterface $subject): StockUnitInterface;

    /**
     * Returns the subject.
     *
     * @return StockSubjectInterface|null
     */
    public function getSubject(): ?StockSubjectInterface;

    /**
     * Returns the geocodes.
     *
     * @return array
     */
    public function getGeocodes(): array;

    /**
     * Returns whether the stock unit has the given geocode.
     *
     * @param string $geocode
     *
     * @return bool
     */
    public function hasGeocode(string $geocode): bool;

    /**
     * Adds the geocode.
     *
     * @param string $geocode
     *
     * @return $this|StockUnitInterface
     */
    public function addGeocode(string $geocode): StockUnitInterface;

    /**
     * Removes the geocode.
     *
     * @param string $geocode
     *
     * @return $this|StockUnitInterface
     */
    public function removeGeocode(string $geocode): StockUnitInterface;

    /**
     * Sets the geocodes.
     *
     * @param array $codes
     *
     * @return $this|StockUnitInterface
     */
    public function setGeocodes(array $codes): StockUnitInterface;

    /**
     * Returns the supplier order item.
     *
     * @return SupplierOrderItemInterface
     */
    public function getSupplierOrderItem(): ?SupplierOrderItemInterface;

    /**
     * Sets the supplier order item.
     *
     * @param SupplierOrderItemInterface|null $item
     *
     * @return $this|StockUnitInterface
     */
    public function setSupplierOrderItem(SupplierOrderItemInterface $item = null);

    /**
     * Returns the estimated date of arrival.
     *
     * @return \DateTime|null
     */
    public function getEstimatedDateOfArrival(): ?\DateTime;

    /**
     * Sets the estimated date of arrival.
     *
     * @param \DateTime|null $date
     *
     * @return $this|StockUnitInterface
     */
    public function setEstimatedDateOfArrival(\DateTime $date = null);

    /**
     * Returns the ordered quantity.
     *
     * @return float
     */
    public function getOrderedQuantity(): float;

    /**
     * Sets the ordered quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockUnitInterface
     */
    public function setOrderedQuantity(float $quantity): StockUnitInterface;

    /**
     * Returns the received quantity.
     *
     * @return float
     */
    public function getReceivedQuantity(): float;

    /**
     * Sets the received quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockUnitInterface
     */
    public function setReceivedQuantity(float $quantity): StockUnitInterface;

    /**
     * Returns the adjusted quantity.
     *
     * @return float
     */
    public function getAdjustedQuantity(): float;

    /**
     * Sets the adjusted quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockUnitInterface
     */
    public function setAdjustedQuantity(float $quantity): StockUnitInterface;

    /**
     * Returns the sold quantity.
     *
     * @return float
     */
    public function getSoldQuantity(): float;

    /**
     * Sets the sold quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockUnitInterface
     */
    public function setSoldQuantity(float $quantity): StockUnitInterface;

    /**
     * Returns the shipped quantity.
     *
     * @return float
     */
    public function getShippedQuantity(): float;

    /**
     * Sets the shipped quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockUnitInterface
     */
    public function setShippedQuantity(float $quantity): StockUnitInterface;

    /**
     * Returns the net price.
     *
     * @return float
     */
    public function getNetPrice(): float;

    /**
     * Sets the net price.
     *
     * @param float $price
     *
     * @return $this|StockUnitInterface
     */
    public function setNetPrice(float $price): StockUnitInterface;

    /**
     * Returns the "created at" date.
     *
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime;

    /**
     * Sets the "created at" date.
     *
     * @param \DateTime $date
     *
     * @return $this|StockUnitInterface
     */
    public function setCreatedAt(\DateTime $date): StockUnitInterface;

    /**
     * Returns the "closed at" date time.
     *
     * @return \DateTime|null
     */
    public function getClosedAt(): ?\DateTime;

    /**
     * Sets the "closed at" at date time.
     *
     * @param \DateTime|null $date
     *
     * @return $this|StockUnitInterface
     */
    public function setClosedAt(\DateTime $date = null): StockUnitInterface;

    /**
     * Returns whether the stock unit has the given stock assignment.
     *
     * @param StockAssignmentInterface $assignment
     *
     * @return bool
     */
    public function hasStockAssignment(StockAssignmentInterface $assignment): bool;

    /**
     * Adds the stock assignments.
     *
     * @param StockAssignmentInterface $assignment
     *
     * @return $this|StockUnitInterface
     */
    public function addStockAssignment(StockAssignmentInterface $assignment): StockUnitInterface;

    /**
     * Removes the stock assignments.
     *
     * @param StockAssignmentInterface $assignment
     *
     * @return $this|StockUnitInterface
     */
    public function removeStockAssignment(StockAssignmentInterface $assignment): StockUnitInterface;

    /**
     * Returns the stock assignments.
     *
     * @return Collection|StockAssignmentInterface[]
     */
    public function getStockAssignments(): Collection;

    /**
     * Returns whether this stock unit has the given stock adjustment.
     *
     * @param StockAdjustmentInterface $adjustment
     *
     * @return bool
     */
    public function hasStockAdjustment(StockAdjustmentInterface $adjustment): bool;

    /**
     * Adds the stock adjustments.
     *
     * @param StockAdjustmentInterface $adjustment
     *
     * @return $this|StockUnitInterface
     */
    public function addStockAdjustment(StockAdjustmentInterface $adjustment): StockUnitInterface;

    /**
     * Removes the stock adjustments.
     *
     * @param StockAdjustmentInterface $adjustment
     *
     * @return $this|StockUnitInterface
     */
    public function removeStockAdjustment(StockAdjustmentInterface $adjustment): StockUnitInterface;

    /**
     * Returns the stock adjustments.
     *
     * @return Collection|StockAdjustmentInterface[]
     */
    public function getStockAdjustments(): Collection;

    /**
     * Returns whether this stock unit is empty (regarding to the ordered and sold quantities).
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Returns whether this stock unit is closed.
     *
     * @return bool
     */
    public function isClosed(): bool;

    /**
     * Returns the reservable stock quantity.
     *
     * @return float
     */
    public function getReservableQuantity(): float;

    /**
     * Returns the shippable stock quantity.
     *
     * @return float
     */
    public function getShippableQuantity(): float;

    /**
     * Returns the supplier order.
     *
     * @return SupplierOrderInterface|null
     */
    public function getSupplierOrder(): ?SupplierOrderInterface;

    /**
     * Returns the currency code.
     *
     * @return string|null
     */
    public function getCurrency(): ?string;

    /**
     * Returns the exchange rate.
     *
     * @return float|null
     */
    public function getExchangeRate(): ?float;

    /**
     * Returns the exchange date.
     *
     * @return \DateTime|null
     */
    public function getExchangeDate(): ?\DateTime;
}
