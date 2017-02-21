<?php

namespace Ekyna\Component\Commerce\Stock\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\StateSubjectTrait;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
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
    protected $id; // TODO Should not be defined here (accessors too...)

    /**
     * @var string
     */
    protected $geocode;

    /**
     * @var SupplierOrderItemInterface
     */
    protected $supplierOrderItem;

    /**
     * @var ArrayCollection|OrderItemInterface[]
     */
    protected $orderItems;

    /**
     * The estimated date of arrival (for ordered quantity).
     * @var \DateTime
     */
    protected $estimatedDateOfArrival;

    /**
     * The quantity ordered to supplier.
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
     * The quantity shipped through sales.
     *
     * @var float
     */
    protected $shippedQuantity = 0;

    /**
     * The quantity shipped through sales.
     *
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
        $this->orderItems = new ArrayCollection();
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
        if ($this->supplierOrderItem != $item) {
            if ($this->supplierOrderItem) {
                $this->supplierOrderItem->setStockUnit(null);
            }

            $this->supplierOrderItem = $item;
            $item->setStockUnit($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addOrderItem(OrderItemInterface $item)
    {
        if (!$this->orderItems->contains($item)) {
            $this->orderItems->add($item);
            $item->addStockUnit($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeOrderItem(OrderItemInterface $item)
    {
        if ($this->orderItems->contains($item)) {
            $this->orderItems->removeElement($item);
            $item->removeStockUnit($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOrderItems()
    {
        return $this->orderItems;
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
    public function getInStockQuantity()
    {
        return $this->getDeliveredQuantity() - $this->getShippedQuantity();
    }
}
