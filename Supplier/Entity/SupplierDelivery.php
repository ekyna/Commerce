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
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        if ($this->id && $this->order) {
            return sprintf('%s#%s', $this->order, $this->id); // TODO created At
        }

        return 'New delivery';
    }

    /**
     * @inheritDoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @inheritDoc
     */
    public function setOrder(Model\SupplierOrderInterface $order = null)
    {
        if ($order !== $this->order) {
            if ($previous = $this->order) {
                $this->order = null;
                $previous->removeDelivery($this);
            }

            if ($this->order = $order) {
                $this->order->addDelivery($this);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasItems()
    {
        return 0 < $this->items->count();
    }

    /**
     * @inheritDoc
     */
    public function hasItem(Model\SupplierDeliveryItemInterface $item)
    {
        return $this->items->contains($item);
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
     */
    public function getItems()
    {
        return $this->items;
    }
}
