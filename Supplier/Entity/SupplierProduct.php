<?php

namespace Ekyna\Component\Commerce\Supplier\Entity;

use DateTime;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeTrait;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class SupplierProduct
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProduct implements SupplierProductInterface
{
    use SubjectRelativeTrait,
        TimestampableTrait;

    /**
     * @var Supplier
     */
    protected $supplier;

    /**
     * @var float
     */
    protected $availableStock;

    /**
     * @var float
     */
    protected $orderedStock;

    /**
     * @var DateTime
     */
    protected $estimatedDateOfArrival;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->initializeSubjectRelative();

        $this->availableStock = 0.;
        $this->orderedStock = 0.;
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->designation ?: 'New supplier product';
    }

    /**
     * @inheritdoc
     */
    public function getSupplier(): ?SupplierInterface
    {
        return $this->supplier;
    }

    /**
     * @inheritdoc
     */
    public function setSupplier(SupplierInterface $supplier)
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAvailableStock(): float
    {
        return $this->availableStock;
    }

    /**
     * @inheritdoc
     */
    public function setAvailableStock(float $stock): SupplierProductInterface
    {
        $this->availableStock = $stock;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOrderedStock(): float
    {
        return $this->orderedStock;
    }

    /**
     * @inheritdoc
     */
    public function setOrderedStock(float $stock): SupplierProductInterface
    {
        $this->orderedStock = $stock;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEstimatedDateOfArrival(): ?DateTime
    {
        return $this->estimatedDateOfArrival;
    }

    /**
     * @inheritdoc
     */
    public function setEstimatedDateOfArrival(DateTime $date = null): SupplierProductInterface
    {
        $this->estimatedDateOfArrival = $date;

        return $this;
    }
}
