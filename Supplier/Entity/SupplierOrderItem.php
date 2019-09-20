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
     * @var int
     */
    protected $id;

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
     * @var string
     */
    protected $designation;

    /**
     * @var string
     */
    protected $reference;

    /**
     * @var float
     */
    protected $quantity = 1;

    /**
     * @var float
     */
    protected $netPrice = 0;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->initializeSubjectIdentity();
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->designation;
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
    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    /**
     * @inheritdoc
     */
    public function setDesignation(string $designation): Model\SupplierOrderItemInterface
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReference(): ?string
    {
        return $this->reference;
    }

    /**
     * @inheritdoc
     */
    public function setReference(string $reference): Model\SupplierOrderItemInterface
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQuantity(): ?float
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

    /**
     * @inheritdoc
     */
    public function getNetPrice(): ?float
    {
        return $this->netPrice;
    }

    /**
     * @inheritdoc
     */
    public function setNetPrice(float $price = null): Model\SupplierOrderItemInterface
    {
        $this->netPrice = $price;

        return $this;
    }
}
