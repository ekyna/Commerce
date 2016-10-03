<?php

namespace Ekyna\Component\Commerce\Supplier\Model;

/**
 * Class SupplierDeliveryItem
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierDeliveryItemInterface
{
    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId();

    /**
     * Returns the supplier delivery.
     *
     * @return SupplierDeliveryInterface
     */
    public function getDelivery();

    /**
     * Sets the supplier delivery.
     *
     * @param SupplierDeliveryInterface $delivery
     *
     * @return $this|SupplierDeliveryItemInterface
     */
    public function setDelivery(SupplierDeliveryInterface $delivery = null);

    /**
     * Returns the supplier order item.
     *
     * @return SupplierOrderItemInterface
     */
    public function getOrderItem();

    /**
     * Sets the supplier order item.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return $this|SupplierDeliveryItemInterface
     */
    public function setOrderItem(SupplierOrderItemInterface $item);

    /**
     * Returns the quantity.
     *
     * @return float
     */
    public function getQuantity();

    /**
     * Sets the quantity.
     *
     * @param float $quantity
     *
     * @return $this|SupplierDeliveryItemInterface
     */
    public function setQuantity($quantity);
}
