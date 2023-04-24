<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Entity;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Supplier\Model;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class SupplierDeliveryItem
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryItem extends AbstractResource implements Model\SupplierDeliveryItemInterface
{
    protected ?Model\SupplierDeliveryInterface  $delivery  = null;
    protected ?Model\SupplierOrderItemInterface $orderItem = null;
    protected Decimal                           $quantity;
    protected ?string                           $geocode   = null;

    public function __construct()
    {
        $this->quantity = new Decimal(0);
    }

    public function getDelivery(): ?Model\SupplierDeliveryInterface
    {
        return $this->delivery;
    }

    public function setDelivery(?Model\SupplierDeliveryInterface $delivery): Model\SupplierDeliveryItemInterface
    {
        if ($delivery === $this->delivery) {
            return $this;
        }

        if ($previous = $this->delivery) {
            $this->delivery = null;
            $previous->removeItem($this);
        }

        if ($this->delivery = $delivery) {
            $this->delivery->addItem($this);
        }

        return $this;
    }

    public function getOrderItem(): Model\SupplierOrderItemInterface
    {
        return $this->orderItem;
    }

    public function setOrderItem(?Model\SupplierOrderItemInterface $item): Model\SupplierDeliveryItemInterface
    {
        $this->orderItem = $item;

        return $this;
    }

    public function getQuantity(): Decimal
    {
        return $this->quantity;
    }

    public function setQuantity(Decimal $quantity): Model\SupplierDeliveryItemInterface
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getGeocode(): ?string
    {
        return $this->geocode;
    }

    public function setGeocode(?string $geocode): Model\SupplierDeliveryItemInterface
    {
        $this->geocode = $geocode;

        return $this;
    }

    public function getSubjectQuantity(): Decimal
    {
        return $this->quantity->mul($this->orderItem->getPacking());
    }
}
