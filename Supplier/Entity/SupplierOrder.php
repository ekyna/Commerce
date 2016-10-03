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
    private $expectedDeliveryDate;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->items = new ArrayCollection();
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
    public function addItem(Model\SupplierOrderItemInterface $item)
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
    public function getExpectedDeliveryDate()
    {
        return $this->expectedDeliveryDate;
    }

    /**
     * @inheritdoc
     */
    public function setExpectedDeliveryDate(\DateTime $date = null)
    {
        $this->expectedDeliveryDate = $date;

        return $this;
    }
}
