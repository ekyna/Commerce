<?php

namespace Ekyna\Component\Commerce\Supplier\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\NumberSubjectInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;

/**
 * Class SupplierOrderInterface
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SupplierOrderInterface extends ResourceInterface, NumberSubjectInterface, TimestampableInterface
{
    /**
     * Returns the currency.
     *
     * @return CurrencyInterface
     */
    public function getCurrency();

    /**
     * Sets the currency.
     *
     * @param CurrencyInterface $currency
     *
     * @return $this|SupplierOrderInterface
     */
    public function setCurrency(CurrencyInterface $currency);

    /**
     * Returns the supplier.
     *
     * @return SupplierInterface
     */
    public function getSupplier();

    /**
     * Sets the supplier.
     *
     * @param SupplierInterface $supplier
     *
     * @return $this|SupplierOrderInterface
     */
    public function setSupplier(SupplierInterface $supplier);

    /**
     * Returns whether or not the supplier order has at least one item.
     *
     * @return bool
     */
    public function hasItems();

    /**
     * Returns whether the supplier order has the item or not.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return bool
     */
    public function hasItem(SupplierOrderItemInterface $item);

    /**
     * Adds the item.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return $this|SupplierOrderInterface
     */
    public function addItem(SupplierOrderItemInterface $item);

    /**
     * Removes the item.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return $this|SupplierOrderInterface
     */
    public function removeItem(SupplierOrderItemInterface $item);

    /**
     * Returns the items.
     *
     * @return ArrayCollection|SupplierOrderItemInterface[]
     */
    public function getItems();

    /**
     * Returns the state.
     *
     * @return string
     */
    public function getState();

    /**
     * Sets the state.
     *
     * @param string $state
     *
     * @return $this|SupplierOrderInterface
     */
    public function setState($state);

    /**
     * Returns the paymentTotal.
     *
     * @return float
     */
    public function getPaymentTotal();

    /**
     * Sets the paymentTotal.
     *
     * @param float $paymentTotal
     *
     * @return $this|SupplierOrderInterface
     */
    public function setPaymentTotal($paymentTotal);

    /**
     * Returns the paymentDate.
     *
     * @return \DateTime
     */
    public function getPaymentDate();

    /**
     * Sets the paymentDate.
     *
     * @param \DateTime $date
     *
     * @return $this|SupplierOrderInterface
     */
    public function setPaymentDate(\DateTime $date = null);

    /**
     * Returns the expectedDeliveryDate.
     *
     * @return \DateTime
     */
    public function getExpectedDeliveryDate();

    /**
     * Sets the expectedDeliveryDate.
     *
     * @param \DateTime $date
     *
     * @return $this|SupplierOrderInterface
     */
    public function setExpectedDeliveryDate(\DateTime $date = null);
}
