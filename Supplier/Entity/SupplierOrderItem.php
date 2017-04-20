<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Entity;

use Decimal\Decimal;
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

    public function __construct()
    {
        $this->initializeSubjectRelative();

        $this->quantity = new Decimal(1);
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
}
