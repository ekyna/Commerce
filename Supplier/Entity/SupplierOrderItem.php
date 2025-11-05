<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Entity;

use Decimal\Decimal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeTrait;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;

/**
 * Class SupplierOrderItem
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItem implements SupplierOrderItemInterface
{
    use SubjectRelativeTrait;

    protected ?SupplierOrderInterface   $order     = null;
    protected ?SupplierProductInterface $product   = null;
    protected ?StockUnitInterface       $stockUnit = null;
    protected Decimal                   $quantity;
    protected Decimal                   $packing;
    protected Collection                $deliveryItems;

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

    public function getOrder(): ?SupplierOrderInterface
    {
        return $this->order;
    }

    public function setOrder(?SupplierOrderInterface $order): SupplierOrderItemInterface
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

    public function getProduct(): ?SupplierProductInterface
    {
        return $this->product;
    }

    public function setProduct(?SupplierProductInterface $product): SupplierOrderItemInterface
    {
        $this->product = $product;

        return $this;
    }

    public function getStockUnit(): ?StockUnitInterface
    {
        return $this->stockUnit;
    }

    public function setStockUnit(?StockUnitInterface $stockUnit): SupplierOrderItemInterface
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

    public function setQuantity(Decimal $quantity): SupplierOrderItemInterface
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPacking(): Decimal
    {
        return $this->packing;
    }

    public function setPacking(Decimal $packing): SupplierOrderItemInterface
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
