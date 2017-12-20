<?php

namespace Ekyna\Component\Commerce\Stock\Model;

use Ekyna\Component\Commerce\Common\Model\StateSubjectInterface;
use Ekyna\Component\Commerce\Stock\Util\StockUtil;
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
    public function setSubject(StockSubjectInterface $subject);

    /**
     * Returns the subject.
     *
     * @return StockSubjectInterface
     */
    public function getSubject();

    /**
     * Returns the geocodes.
     *
     * @return array
     */
    public function getGeocodes();

    /**
     * Returns whether the stock unit has the given geocode.
     *
     * @param string $geocode
     *
     * @return bool
     */
    public function hasGeocode($geocode);

    /**
     * Adds the geocode.
     *
     * @param string $geocode
     *
     * @return $this|StockUnitInterface
     */
    public function addGeocode($geocode);

    /**
     * Removes the geocode.
     *
     * @param string $geocode
     *
     * @return $this|StockUnitInterface
     */
    public function removeGeocode($geocode);

    /**
     * Sets the geocodes.
     *
     * @param array $codes
     *
     * @return $this|StockUnitInterface
     */
    public function setGeocodes(array $codes);

    /**
     * Returns the supplierOrderItem.
     *
     * @return SupplierOrderItemInterface
     */
    public function getSupplierOrderItem();

    /**
     * Sets the supplierOrderItem.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return $this|StockUnitInterface
     */
    public function setSupplierOrderItem(SupplierOrderItemInterface $item = null);

    /**
     * Returns the ordered quantity.
     *
     * @return float
     */
    public function getOrderedQuantity();

    /**
     * Sets the ordered quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockUnitInterface
     */
    public function setOrderedQuantity($quantity);

    /**
     * Returns the estimated date of arrival.
     *
     * @return \DateTime
     */
    public function getEstimatedDateOfArrival();

    /**
     * Sets the estimated date of arrival.
     *
     * @param \DateTime $date
     *
     * @return $this|StockUnitInterface
     */
    public function setEstimatedDateOfArrival(\DateTime $date = null);

    /**
     * Returns the received quantity.
     *
     * @return float
     */
    public function getReceivedQuantity();

    /**
     * Sets the received quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockUnitInterface
     */
    public function setReceivedQuantity($quantity);

    /**
     * Returns the sold quantity.
     *
     * @return float
     */
    public function getSoldQuantity();

    /**
     * Sets the sold quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockUnitInterface
     */
    public function setSoldQuantity($quantity);

    /**
     * Returns the shipped quantity.
     *
     * @return float
     */
    public function getShippedQuantity();

    /**
     * Sets the shipped quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockUnitInterface
     */
    public function setShippedQuantity($quantity);

    /**
     * Returns the net price.
     *
     * @return float
     */
    public function getNetPrice();

    /**
     * Sets the net price.
     *
     * @param float $price
     *
     * @return $this|StockUnitInterface
     */
    public function setNetPrice($price);

    /**
     * Returns the "created at" date.
     *
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * Sets the "created at" date.
     *
     * @param \DateTime $date
     *
     * @return $this|StockUnitInterface
     */
    public function setCreatedAt(\DateTime $date = null);

    /**
     * Returns the "closed at" date time.
     *
     * @return \DateTime
     */
    public function getClosedAt();

    /**
     * Sets the "closed at" at date time.
     *
     * @param \DateTime $date
     *
     * @return $this|StockUnitInterface
     */
    public function setClosedAt(\DateTime $date = null);

    /**
     * Adds the stock assignments.
     *
     * @param StockAssignmentInterface $assignment
     *
     * @return $this|StockUnitInterface
     */
    public function addStockAssignment(StockAssignmentInterface $assignment);

    /**
     * Removes the stock assignments.
     *
     * @param StockAssignmentInterface $assignment
     *
     * @return $this|StockUnitInterface
     */
    public function removeStockAssignment(StockAssignmentInterface $assignment);

    /**
     * Returns the stock assignments.
     *
     * @return \Doctrine\Common\Collections\Collection|StockAssignmentInterface[]
     */
    public function getStockAssignments();

    /**
     * Returns whether the stock unit is empty (regarding to the ordered and sold quantities).
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Returns the reservable stock quantity.
     *
     * @see StockUtil::calculateReservable()
     *
     * @return float
     */
    public function getReservableQuantity();

    /**
     * Returns the shippable stock quantity.
     *
     * @see StockUtil::calculateShippable()
     *
     * @return float
     */
    public function getShippableQuantity();
}
