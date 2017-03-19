<?php

namespace Ekyna\Component\Commerce\Supplier\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Supplier\Model;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class SupplierDelivery
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDelivery implements Model\SupplierDeliveryInterface
{
    use TimestampableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\SupplierOrderInterface
     */
    protected $order;

    /**
     * @var ArrayCollection|Model\SupplierDeliveryItemInterface[]
     */
    protected $items;


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
        return $this->getOrder()->getNumber() . '#' . $this->getId(); // TODO created At
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
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @inheritdoc
     */
    public function setOrder(Model\SupplierOrderInterface $order = null)
    {
        if ($order != $this->order) {
            if ($this->order) {
                $this->order->removeDelivery($this);
            }

            $this->order = $order;

            if (null !== $order) {
                $order->addDelivery($this);
            }
        }

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
    public function hasItem(Model\SupplierDeliveryItemInterface $item)
    {
        return $this->items->contains($item);
    }

    /**
     * @inheritdoc
     */
    public function addItem(Model\SupplierDeliveryItemInterface $item)
    {
        if (!$this->hasItem($item)) {
            $this->items->add($item);
            $item->setDelivery($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeItem(Model\SupplierDeliveryItemInterface $item)
    {
        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
            $item->setDelivery(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->items;
    }
}
