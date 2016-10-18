<?php

namespace Ekyna\Component\Commerce\Supplier\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Supplier\Model;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class SupplierOrder
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrder implements Model\SupplierOrderInterface
{
    use TimestampableTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $number;

    /**
     * @var CurrencyInterface
     */
    private $currency;

    /**
     * @var Model\SupplierInterface
     */
    private $supplier;

    /**
     * @var ArrayCollection|Model\SupplierOrderItemInterface[]
     */
    private $items;

    /**
     * @var ArrayCollection|Model\SupplierDeliveryInterface[]
     */
    private $deliveries;

    /**
     * @var string
     */
    private $state;

    /**
     * @var float
     */
    private $paymentTotal;

    /**
     * @var \DateTime
     */
    private $paymentDate;

    /**
     * @var \DateTime
     */
    private $estimatedDateOfArrival;

    /**
     * @var \DateTime
     */
    private $orderedAt;

    /**
     * @var \DateTime
     */
    private $completedAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->state = Model\SupplierOrderStates::STATE_NEW;

        $this->items = new ArrayCollection();
        $this->deliveries = new ArrayCollection();
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->getNumber();
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
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @inheritdoc
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @inheritdoc
     */
    public function setCurrency(CurrencyInterface $currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @inheritdoc
     */
    public function setSupplier(Model\SupplierInterface $supplier)
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasItems()
    {
        return 0 < $this->items->count();
    }

    /**
     * @inheritdoc
     */
    public function hasItem(Model\SupplierOrderItemInterface $item)
    {
        return $this->items->contains($item);
    }

    /**
     * @inheritdoc
     */
    public function addItem(Model\SupplierOrderItemInterface $item, $index = null)
    {
        if (!$this->hasItem($item)) {
            $item->setOrder($this);
            $this->items->add($item);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeItem(Model\SupplierOrderItemInterface $item)
    {
        if ($this->hasItem($item)) {
            $item->setOrder(null);
            $this->items->removeElement($item);
        }

        return $this;
    }

    /**
     * Returns the items.
     *
     * @return ArrayCollection|Model\SupplierOrderItemInterface[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function hasDeliveries()
    {
        return 0 < $this->deliveries->count();
    }

    /**
     * @inheritdoc
     */
    public function hasDelivery(Model\SupplierDeliveryInterface $delivery)
    {
        return $this->deliveries->contains($delivery);
    }

    /**
     * @inheritdoc
     */
    public function addDelivery(Model\SupplierDeliveryInterface $delivery)
    {
        if (!$this->hasDelivery($delivery)) {
            $delivery->setOrder($this);
            $this->deliveries->add($delivery);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeDelivery(Model\SupplierDeliveryInterface $delivery)
    {
        if ($this->hasDelivery($delivery)) {
            $delivery->setOrder(null);
            $this->deliveries->removeElement($delivery);
        }

        return $this;
    }

    /**
     * Returns the deliveries.
     *
     * @return ArrayCollection|Model\SupplierDeliveryInterface[]
     */
    public function getDeliveries()
    {
        return $this->deliveries;
    }

    /**
     * @inheritdoc
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @inheritdoc
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentTotal()
    {
        return $this->paymentTotal;
    }

    /**
     * @inheritdoc
     */
    public function setPaymentTotal($paymentTotal)
    {
        $this->paymentTotal = (float)$paymentTotal;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * @inheritdoc
     */
    public function setPaymentDate(\DateTime $date = null)
    {
        $this->paymentDate = $date;

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
    public function getOrderedAt()
    {
        return $this->orderedAt;
    }

    /**
     * @inheritdoc
     */
    public function setOrderedAt(\DateTime $orderedAt = null)
    {
        $this->orderedAt = $orderedAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCompletedAt()
    {
        return $this->completedAt;
    }

    /**
     * @inheritdoc
     */
    public function setCompletedAt(\DateTime $completedAt = null)
    {
        $this->completedAt = $completedAt;

        return $this;
    }
}
