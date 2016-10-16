<?php

namespace Ekyna\Component\Commerce\Supplier\Model;

use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class SupplierOrderItemInterface
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierOrderItemInterface extends ResourceInterface, SubjectRelativeInterface
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
     * @return $this|SupplierOrderItemInterface
     */
    public function setOrder(SupplierOrderInterface $order = null);

    /**
     * Returns the supplier product.
     *
     * @return SupplierProductInterface
     */
    public function getProduct();

    /**
     * Sets the supplier product.
     *
     * @param SupplierProductInterface $product
     *
     * @return $this|SupplierOrderItemInterface
     */
    public function setProduct(SupplierProductInterface $product = null);

    /**
     * Returns the designation.
     *
     * @return string
     */
    public function getDesignation();

    /**
     * Sets the designation.
     *
     * @param string $designation
     *
     * @return $this|SupplierOrderItemInterface
     */
    public function setDesignation($designation);

    /**
     * Returns the reference.
     *
     * @return string
     */
    public function getReference();

    /**
     * Sets the reference.
     *
     * @param string $reference
     *
     * @return $this|SupplierOrderItemInterface
     */
    public function setReference($reference);

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
     * @return $this|SupplierOrderItemInterface
     */
    public function setQuantity($quantity);

    /**
     * Returns the delivery remaining quantity.
     *
     * @param SupplierDeliveryInterface $delivery
     *
     * @return float
     */
    public function getDeliveryRemainingQuantity(SupplierDeliveryInterface $delivery = null);

    /**
     * Returns the net price.
     *
     * @return float
     */
    public function getNetPrice();

    /**
     * Sets the net price.
     *
     * @param float $netPrice
     *
     * @return $this|SupplierOrderItemInterface
     */
    public function setNetPrice($netPrice);
}
