<?php

namespace Ekyna\Component\Commerce\Stock\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\StateSubjectTrait;
use Ekyna\Component\Commerce\Stock\Model;
use Ekyna\Component\Commerce\Stock\Util\StockUtil;
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
     * @var string
     */
    protected $geocode;

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
     * Constructor.
     */
    public function __construct()
    {
        $this->state = Model\StockUnitStates::STATE_NEW;
        $this->createdAt = new \DateTime();
        $this->stockAssignments = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        if (0 < strlen($this->getGeocode())) {
            return $this->getGeocode();
        } elseif (0 < $this->getId()) {
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
    public function getGeocode()
    {
        return $this->geocode;
    }

    /**
     * @inheritdoc
     */
    public function setGeocode($code)
    {
        $this->geocode = $code;

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
        if ($item !== $previous = $this->supplierOrderItem) {
            if ($previous) {
                $previous->setStockUnit(null);
            }

            $this->supplierOrderItem = $item;

            if ($this->supplierOrderItem) {
                $this->supplierOrderItem->setStockUnit($this);
            }
        }

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
    public function getSoldQuantity()
    {
        return $this->soldQuantity;
    }

    /**
     * @inheritdoc
     */
    public function setSoldQuantity($quantity)
    {
        $this->soldQuantity = $quantity;

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
        $this->netPrice = $price;

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
    public function addStockAssignment(Model\StockAssignmentInterface $assignment)
    {
        if (!$this->stockAssignments->contains($assignment)) {
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
        if ($this->stockAssignments->contains($assignment)) {
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
     * @inheritdoc
     */
    public function isEmpty()
    {
        return 0 == $this->orderedQuantity && 0 == $this->soldQuantity;
    }

    /**
     * @inheritdoc
     */
    public function getReservableQuantity()
    {
        return StockUtil::calculateReservable(
            $this->orderedQuantity,
            $this->soldQuantity
        );
    }

    /**
     * @inheritdoc
     */
    public function getShippableQuantity()
    {
        return StockUtil::calculateShippable(
            $this->receivedQuantity,
            $this->soldQuantity,
            $this->shippedQuantity
        );
    }
}
