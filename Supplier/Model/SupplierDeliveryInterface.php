<?php

namespace Ekyna\Component\Commerce\Supplier\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Resource\Model as ResourceModel;

/**
 * Class SupplierDelivery
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierDeliveryInterface extends ResourceModel\ResourceInterface , ResourceModel\TimestampableInterface
{
    /**
     * Returns the supplier order.
     *
     * @return SupplierOrderInterface
     */
    public function getOrder();

    /**
     * Sets the supplier order.
     *
     * @param SupplierOrderInterface $order
     *
     * @return $this|SupplierDeliveryInterface
     */
    public function setOrder(SupplierOrderInterface $order);

    /**
     * Returns whether or not the supplier delivery has items.
     *
     * @return bool
     */
    public function hasItems();

    /**
     * Returns whether or not the supplier delivery has the given item or not.
     *
     * @param SupplierDeliveryItemInterface $item
     *
     * @return $this|SupplierDeliveryInterface
     */
    public function hasItem(SupplierDeliveryItemInterface $item);

    /**
     * Adds the item.
     *
     * @param SupplierDeliveryItemInterface $item
     *
     * @return $this|SupplierDeliveryInterface
     */
    public function addItem(SupplierDeliveryItemInterface $item);

    /**
     * Removes the item.
     *
     * @param SupplierDeliveryItemInterface $item
     *
     * @return $this|SupplierDeliveryInterface
     */
    public function removeItem(SupplierDeliveryItemInterface $item);

    /**
     * Returns the items.
     *
     * @return ArrayCollection|SupplierDeliveryItemInterface[]
     */
    public function getItems();
}
