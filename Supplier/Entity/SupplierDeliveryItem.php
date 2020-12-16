<?php

namespace Ekyna\Component\Commerce\Supplier\Entity;

use Ekyna\Component\Commerce\Supplier\Model;

/**
 * Class SupplierDeliveryItem
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryItem implements Model\SupplierDeliveryItemInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\SupplierDeliveryInterface
     */
    protected $delivery;

    /**
     * @var Model\SupplierOrderItemInterface
     */
    protected $orderItem;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * @var string
     */
    protected $geocode;


    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getDelivery()
    {
        return $this->delivery;
    }

    /**
     * @inheritdoc
     */
    public function setDelivery(Model\SupplierDeliveryInterface $delivery = null)
    {
        if ($delivery !== $this->delivery) {
            if ($previous = $this->delivery) {
                $this->delivery = null;
                $previous->removeItem($this);
            }

            if ($this->delivery = $delivery) {
                $this->delivery->addItem($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @inheritdoc
     */
    public function setOrderItem(Model\SupplierOrderItemInterface $item = null)
    {
        $this->orderItem = $item;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @inheritdoc
     */
    public function setQuantity($quantity)
    {
        $this->quantity = (float)$quantity;

        return $this;
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
    public function setGeocode($geocode)
    {
        $this->geocode = $geocode;

        return $this;
    }
}
