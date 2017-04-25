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
     * @var ArrayCollection|Model\StockAssignmentInterface[]
     */
    protected $stockAssignments;

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
     * The quantity delivered by supplier.
     *
     * @var float
     */
    protected $deliveredQuantity = 0;

    /**
     * The quantity reserved from sales.
     *
     * @var float
     */
    protected $reservedQuantity = 0;

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
     * Constructor.
     */
    public function __construct()
    {
        $this->stockAssignments = new ArrayCollection();
        $this->state = Model\StockUnitStates::STATE_NEW;
        $this->createdAt = new \DateTime();
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
        if ($this->supplierOrderItem !== $item) {
            $previous = $this->supplierOrderItem;
            $this->supplierOrderItem = $item;

            if ($previous) {
                $previous->setStockUnit(null);
            }

            if ($this->supplierOrderItem) {
                $this->supplierOrderItem->setStockUnit($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addStockAssignment(Model\StockAssignmentInterface $item)
    {
        if (!$this->stockAssignments->contains($item)) {
            $this->stockAssignments->add($item);
            $item->setStockUnit($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeStockAssignment(Model\StockAssignmentInterface $item)
    {
        if ($this->stockAssignments->contains($item)) {
            $this->stockAssignments->removeElement($item);
            $item->setStockUnit(null);
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
    public function getDeliveredQuantity()
    {
        return $this->deliveredQuantity;
    }

    /**
     * @inheritdoc
     */
    public function setDeliveredQuantity($quantity)
    {
        $this->deliveredQuantity = (float)$quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReservedQuantity()
    {
        return $this->reservedQuantity;
    }

    /**
     * @inheritdoc
     */
    public function setReservedQuantity($quantity)
    {
        $this->reservedQuantity = $quantity;

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
    public function isEmpty()
    {
        return 0 == $this->orderedQuantity && 0 == $this->reservedQuantity;
    }

    /**
     * @inheritdoc
     */
    public function getInStockQuantity()
    {
        return StockUtil::calculateInStock(
            $this->deliveredQuantity,
            $this->reservedQuantity
        );
    }

    /**
     * @inheritdoc
     */
    public function getVirtualStockQuantity()
    {
        return StockUtil::calculateVirtualStock(
            $this->orderedQuantity,
            $this->deliveredQuantity,
            $this->reservedQuantity
        );
    }

    /**
     * @inheritdoc
     */
    public function getReservableQuantity()
    {
        return StockUtil::calculateReservable(
            $this->orderedQuantity,
            $this->reservedQuantity
        );
    }

    /**
     * @inheritdoc
     */
    public function getShippableQuantity()
    {
        return StockUtil::calculateShippable(
            $this->deliveredQuantity,
            $this->reservedQuantity,
            $this->shippedQuantity
        );
    }
}
