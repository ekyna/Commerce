<?php

namespace Ekyna\Component\Commerce\Supplier\Entity;

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

    /**
     * @var Model\SupplierOrderInterface
     */
    protected $order;

    /**
     * @var Model\SupplierProductInterface
     */
    protected $product;

    /**
     * @var StockUnitInterface
     */
    protected $stockUnit;

    /**
     * @var float
     */
    protected $quantity;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->initializeSubjectRelative();

        $this->quantity = 1.;
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->designation ?: 'New supplier order item';
    }

    /**
     * @inheritdoc
     */
    public function getOrder(): ?Model\SupplierOrderInterface
    {
        return $this->order;
    }

    /**
     * @inheritdoc
     */
    public function setOrder(Model\SupplierOrderInterface $order = null): Model\SupplierOrderItemInterface
    {
        if ($this->order !== $order) {
            if ($previous = $this->order) {
                $this->order = null;
                $previous->removeItem($this);
            }

            if ($this->order = $order) {
                $this->order->addItem($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProduct(): ?Model\SupplierProductInterface
    {
        return $this->product;
    }

    /**
     * @inheritdoc
     */
    public function setProduct(Model\SupplierProductInterface $product = null): Model\SupplierOrderItemInterface
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStockUnit(): ?StockUnitInterface
    {
        return $this->stockUnit;
    }

    /**
     * @inheritdoc
     */
    public function setStockUnit(StockUnitInterface $stockUnit = null): Model\SupplierOrderItemInterface
    {
        if ($this->stockUnit !== $stockUnit) {
            if ($previous = $this->stockUnit) {
                $this->stockUnit = null;
                $previous->setSupplierOrderItem(null);
            }

            if ($this->stockUnit = $stockUnit) {
                $this->stockUnit->setSupplierOrderItem($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }

    /**
     * @inheritdoc
     */
    public function setQuantity(float $quantity): Model\SupplierOrderItemInterface
    {
        $this->quantity = (float)$quantity;

        return $this;
    }
}
