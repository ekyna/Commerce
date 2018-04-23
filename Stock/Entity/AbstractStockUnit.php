<?php

namespace Ekyna\Component\Commerce\Stock\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\StateSubjectTrait;
use Ekyna\Component\Commerce\Stock\Model;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;

/**
 * Class AbstractStockUnit
 * @package Ekyna\Component\Commerce\Stock\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractStockUnit implements Model\StockUnitInterface
{
    use StateSubjectTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var array
     */
    protected $geocodes;

    /**
     * @var SupplierOrderItemInterface
     */
    protected $supplierOrderItem;

    /**
     * The estimated date of arrival (for ordered quantity).
     *
     * @var \DateTime
     */
    protected $estimatedDateOfArrival;

    /**
     * The quantity ordered to supplier.
     *
     * @var float
     */
    protected $orderedQuantity = 0;

    /**
     * The quantity received by supplier.
     *
     * @var float
     */
    protected $receivedQuantity = 0;

    /**
     * The quantity adjusted by administrators.
     *
     * @var float
     */
    protected $adjustedQuantity = 0;

    /**
     * The quantity sold from sales.
     *
     * @var float
     */
    protected $soldQuantity = 0;

    /**
     * The quantity shipped through sales.
     *
     * @var float
     */
    protected $shippedQuantity = 0;

    /**
     * @var float
     */
    protected $netPrice = 0;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $closedAt;

    /**
     * @var ArrayCollection|Model\StockAssignmentInterface[]
     */
    protected $stockAssignments;

    /**
     * @var ArrayCollection|Model\StockAdjustmentInterface[]
     */
    protected $stockAdjustments;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->state = Model\StockUnitStates::STATE_NEW;
        $this->geocodes = [];
        $this->createdAt = new \DateTime();
        $this->stockAssignments = new ArrayCollection();
        $this->stockAdjustments = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        if (!empty($this->getGeocodes())) {
            return implode('-', $this->getGeocodes());
        } elseif (null !== $this->getId()) {
            return '#'.$this->getId();
        }

        return 'Unknown';
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @inheritdoc
     */
    public function getGeocodes()
    {
        return $this->geocodes;
    }

    /**
     * @inheritdoc
     */
    public function hasGeocode($geocode)
    {
        $geocode = strtoupper($geocode);

        return in_array($geocode, $this->geocodes, true);
    }

    /**
     * @inheritdoc
     */
    public function addGeocode($geocode)
    {
        $geocode = strtoupper($geocode);

        if (!in_array($geocode, $this->geocodes, true)) {
            $this->geocodes[] = $geocode;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeGeocode($geocode)
    {
        $geocode = strtoupper($geocode);

        if (false !== $index = array_search($geocode, $this->geocodes, true)) {
            unset($this->geocodes[$index]);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setGeocodes(array $codes)
    {
        $this->geocodes = $codes;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSupplierOrderItem()
    {
        return $this->supplierOrderItem;
    }

    /**
     * @inheritdoc
     */
    public function setSupplierOrderItem(SupplierOrderItemInterface $item = null)
    {
        if ($item !== $this->supplierOrderItem) {
            if ($previous = $this->supplierOrderItem) {
                $this->supplierOrderItem = null;
                $previous->setStockUnit(null);
            }

            if ($this->supplierOrderItem = $item) {
                $this->supplierOrderItem->setStockUnit($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOrderedQuantity()
    {
        return $this->orderedQuantity;
    }

    /**
     * @inheritdoc
     */
    public function setOrderedQuantity($quantity)
    {
        $this->orderedQuantity = (float)$quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReceivedQuantity()
    {
        return $this->receivedQuantity;
    }

    /**
     * @inheritdoc
     */
    public function setReceivedQuantity($quantity)
    {
        $this->receivedQuantity = (float)$quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAdjustedQuantity()
    {
        return $this->adjustedQuantity;
    }

    /**
     * @inheritdoc
     */
    public function setAdjustedQuantity($quantity)
    {
        $this->adjustedQuantity = (float)$quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSoldQuantity()
    {
        return $this->soldQuantity;
    }

    /**
     * @inheritdoc
     */
    public function setSoldQuantity($quantity)
    {
        $this->soldQuantity = (float)$quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShippedQuantity()
    {
        return $this->shippedQuantity;
    }

    /**
     * @inheritdoc
     */
    public function setShippedQuantity($quantity)
    {
        $this->shippedQuantity = (float)$quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * @inheritdoc
     */
    public function setNetPrice($price)
    {
        $this->netPrice = (float)$price;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEstimatedDateOfArrival()
    {
        return $this->estimatedDateOfArrival;
    }

    /**
     * @inheritdoc
     */
    public function setEstimatedDateOfArrival(\DateTime $date = null)
    {
        $this->estimatedDateOfArrival = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(\DateTime $date = null)
    {
        $this->createdAt = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getClosedAt()
    {
        return $this->closedAt;
    }

    /**
     * @inheritdoc
     */
    public function setClosedAt(\DateTime $date = null)
    {
        $this->closedAt = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasStockAssignment(Model\StockAssignmentInterface $assignment)
    {
        return $this->stockAssignments->contains($assignment);
    }

    /**
     * @inheritdoc
     */
    public function addStockAssignment(Model\StockAssignmentInterface $assignment)
    {
        if (!$this->hasStockAssignment($assignment)) {
            $this->stockAssignments->add($assignment);
            $assignment->setStockUnit($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeStockAssignment(Model\StockAssignmentInterface $assignment)
    {
        if ($this->hasStockAssignment($assignment)) {
            $this->stockAssignments->removeElement($assignment);
            $assignment->setStockUnit(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStockAssignments()
    {
        return $this->stockAssignments;
    }

    /**
     * @inheritDoc
     */
    public function hasStockAdjustment(Model\StockAdjustmentInterface $adjustment)
    {
        return $this->stockAdjustments->contains($adjustment);
    }

    /**
     * @inheritdoc
     */
    public function addStockAdjustment(Model\StockAdjustmentInterface $adjustment)
    {
        if (!$this->hasStockAdjustment($adjustment)) {
            $this->stockAdjustments->add($adjustment);
            $adjustment->setStockUnit($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeStockAdjustment(Model\StockAdjustmentInterface $adjustment)
    {
        if ($this->hasStockAdjustment($adjustment)) {
            $this->stockAdjustments->removeElement($adjustment);
            $adjustment->setStockUnit(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStockAdjustments()
    {
        return $this->stockAdjustments;
    }

    /**
     * @inheritdoc
     */
    public function isEmpty()
    {
        return null === $this->supplierOrderItem
            && 0 == $this->stockAssignments->count()
            && 0 == $this->orderedQuantity
            && 0 == $this->soldQuantity
            && 0 == $this->adjustedQuantity;
    }

    /**
     * @inheritDoc
     */
    public function isClosed()
    {
        return $this->state === Model\StockUnitStates::STATE_CLOSED;
    }

    /**
     * @inheritdoc
     */
    public function getReservableQuantity()
    {
        if (0 == $this->orderedQuantity) {
            return INF;
        }

        $result = $this->orderedQuantity + $this->adjustedQuantity - $this->soldQuantity;

        return max($result, 0);
    }

    /**
     * @inheritdoc
     */
    public function getShippableQuantity()
    {
        $result = $this->receivedQuantity + $this->adjustedQuantity - $this->shippedQuantity;

        return max($result, 0);
    }
}
