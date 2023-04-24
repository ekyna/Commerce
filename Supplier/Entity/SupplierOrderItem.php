<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Entity;

use Decimal\Decimal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeTrait;
use Ekyna\Component\Commerce\Supplier\Model;

/**
 * Class SupplierOrderItem
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItem implements Model\SupplierOrderItemInterface
{
    use SubjectRelativeTrait;

    protected ?Model\SupplierOrderInterface   $order     = null;
    protected ?Model\SupplierProductInterface $product   = null;
    protected ?StockUnitInterface             $stockUnit = null;
    protected Decimal                         $quantity;
    protected Decimal                         $packing;
    protected Collection                      $deliveryItems;

    public function __construct()
    {
        $this->initializeSubjectRelative();

        $this->quantity = new Decimal(1);
        $this->packing = new Decimal(1);
        $this->deliveryItems = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->designation ?: 'New supplier order item';
    }

    public function getOrder(): ?Model\SupplierOrderInterface
    {
        return $this->order;
    }

    public function setOrder(?Model\SupplierOrderInterface $order): Model\SupplierOrderItemInterface
    {
        if ($this->order === $order) {
            return $this;
        }

        if ($previous = $this->order) {
            $this->order = null;
            $previous->removeItem($this);
        }

        if ($this->order = $order) {
            $this->order->addItem($this);
        }

        return $this;
    }

    public function getProduct(): ?Model\SupplierProductInterface
    {
        return $this->product;
    }

    public function setProduct(?Model\SupplierProductInterface $product): Model\SupplierOrderItemInterface
    {
        $this->product = $product;

        return $this;
    }

    public function getStockUnit(): ?StockUnitInterface
    {
        return $this->stockUnit;
    }

    public function setStockUnit(?StockUnitInterface $stockUnit): Model\SupplierOrderItemInterface
    {
        if ($this->stockUnit === $stockUnit) {
            return $this;
        }

        if ($previous = $this->stockUnit) {
            $this->stockUnit = null;
            $previous->setSupplierOrderItem(null);
        }

        if ($this->stockUnit = $stockUnit) {
            $this->stockUnit->setSupplierOrderItem($this);
        }

        return $this;
    }

    public function getQuantity(): Decimal
    {
        return $this->quantity;
    }

    public function setQuantity(Decimal $quantity): Model\SupplierOrderItemInterface
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPacking(): Decimal
    {
        return $this->packing;
    }

    public function setPacking(Decimal $packing): Model\SupplierOrderItemInterface
    {
        $this->packing = $packing;

        return $this;
    }

    public function getDeliveryItems(): Collection
    {
        return $this->deliveryItems;
    }

    public function getSubjectQuantity(): Decimal
    {
        return $this->quantity->mul($this->packing);
    }
}
