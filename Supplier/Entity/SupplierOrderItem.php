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
        $this->order = $order;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @inheritdoc
     */
    public function setProduct(Model\SupplierProductInterface $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStockUnit()
    {
        return $this->stockUnit;
    }

    /**
     * @inheritdoc
     */
    public function setStockUnit(StockUnitInterface $stockUnit = null)
    {
        if ($this->stockUnit != $stockUnit) {
            if ($this->stockUnit) {
                $this->stockUnit->setSupplierOrderItem(null);
            }

            $this->stockUnit = $stockUnit;

            if ($stockUnit) {
                $stockUnit->setSupplierOrderItem($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * @inheritdoc
     */
    public function setDesignation($designation)
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @inheritdoc
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

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
    public function getDeliveryRemainingQuantity(Model\SupplierDeliveryInterface $delivery = null)
    {
        $remainingQuantity = $this->getQuantity();

        foreach ($this->getOrder()->getDeliveries() as $orderDelivery) {
            if ($delivery && $delivery->getId() && $delivery->getId() == $orderDelivery->getId()) {
                continue;
            }
            foreach ($orderDelivery->getItems() as $deliveryItem) {
                if ($this === $deliveryItem->getOrderItem()) {
                    $remainingQuantity -= $deliveryItem->getQuantity();
                    continue 2;
                }
            }
        }

        return $remainingQuantity;
    }

    /**
     * @inheritdoc
     */
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * @inheritdoc
     */
    public function setNetPrice($netPrice)
    {
        $this->netPrice = (float)$netPrice;

        return $this;
    }
}
