<?php

namespace Ekyna\Component\Commerce\Stock\Model;

use Ekyna\Component\Commerce\Common\Model\StateSubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface StockUnitInterface
 * @package Ekyna\Component\Commerce\Stock\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockUnitInterface extends ResourceInterface, StateSubjectInterface, StockAssignmentsInterface
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
     * Returns the geocode.
     *
     * @return string
     */
    public function getGeocode();

    /**
     * Sets the geocode.
     *
     * @param string $code
     *
     * @return $this|StockUnitInterface
     */
    public function setGeocode($code);

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
     * Returns the delivered quantity.
     *
     * @return float
     */
    public function getDeliveredQuantity();

    /**
     * Sets the delivered quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockUnitInterface
     */
    public function setDeliveredQuantity($quantity);

    /**
     * Returns the reserved quantity.
     *
     * @return float
     */
    public function getReservedQuantity();

    /**
     * Sets the reserved quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockUnitInterface
     */
    public function setReservedQuantity($quantity);

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
     * Returns whether the stock unit is empty (regarding to the ordered and reserved quantities).
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Returns the in stock quantity (delivered - reserved, 0 or greater).
     *
     * @return float
     */
    public function getInStockQuantity();

    /**
     * Returns the virtual stock quantity (ordered - max(delivered or reserved), 0 or greater).
     *
     * @return float
     */
    public function getVirtualStockQuantity();
}
