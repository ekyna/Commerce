<?php

namespace Ekyna\Component\Commerce\Supplier\Model;

use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;
use DateTime;

/**
 * Interface SupplierProductInterface
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierProductInterface extends SubjectRelativeInterface, TimestampableInterface
{
    /**
     * Returns the supplier.
     *
     * @return SupplierInterface|null
     */
    public function getSupplier(): ?SupplierInterface;

    /**
     * Sets the supplier.
     *
     * @param SupplierInterface $supplier
     *
     * @return $this|SupplierProductInterface
     */
    public function setSupplier(SupplierInterface $supplier);

    /**
     * Returns the available stock.
     *
     * @return float
     */
    public function getAvailableStock(): float;

    /**
     * Sets the available stock.
     *
     * @param float $stock
     *
     * @return $this|SupplierProductInterface
     */
    public function setAvailableStock(float $stock): SupplierProductInterface;

    /**
     * Returns the ordered stock.
     *
     * @return float
     */
    public function getOrderedStock(): float;

    /**
     * Sets the ordered stock.
     *
     * @param float $stock
     *
     * @return $this|SupplierProductInterface
     */
    public function setOrderedStock(float $stock): SupplierProductInterface;

    /**
     * Returns the estimated date of arrival.
     *
     * @return DateTime
     */
    public function getEstimatedDateOfArrival(): ?DateTime;

    /**
     * Sets the estimated date of arrival.
     *
     * @param \DateTime $date
     *
     * @return $this|SupplierProductInterface
     */
    public function setEstimatedDateOfArrival(DateTime $date = null): SupplierProductInterface;
}
