<?php

namespace Ekyna\Component\Commerce\Supplier\Model;

use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;

/**
 * Interface SupplierProductInterface
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierProductInterface extends SubjectRelativeInterface, ResourceInterface, TimestampableInterface
{
    /**
     * Returns the supplier.
     *
     * @return SupplierInterface
     */
    public function getSupplier();

    /**
     * Sets the supplier.
     *
     * @param SupplierInterface $supplier
     *
     * @return $this|SupplierProductInterface
     */
    public function setSupplier(SupplierInterface $supplier);

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
     *
     * @return $this|SupplierProductInterface
     */
    public function setDesignation($designation);

    /**
     * Returns the reference.
     *
     * @return string
     */
    public function getReference();

    /**
     * Sets the reference.
     *
     * @param string $reference
     *
     * @return $this|SupplierProductInterface
     */
    public function setReference($reference);

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
     * @return $this|SupplierProductInterface
     */
    public function setNetPrice($price);

    /**
     * Returns the weight (kilograms).
     *
     * @return float
     */
    public function getWeight();

    /**
     * Sets the weight (kilograms).
     *
     * @param float $weight
     *
     * @return $this|SupplierProductInterface
     */
    public function setWeight($weight);

    /**
     * Returns the available stock.
     *
     * @return float
     */
    public function getAvailableStock();

    /**
     * Sets the available stock.
     *
     * @param float $stock
     *
     * @return $this|SupplierProductInterface
     */
    public function setAvailableStock($stock);

    /**
     * Returns the ordered stock.
     *
     * @return float
     */
    public function getOrderedStock();

    /**
     * Sets the ordered stock.
     *
     * @param float $stock
     *
     * @return $this|SupplierProductInterface
     */
    public function setOrderedStock($stock);

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
     * @return $this|SupplierProductInterface
     */
    public function setEstimatedDateOfArrival(\DateTime $date = null);
}
