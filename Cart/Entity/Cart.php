<?php

namespace Ekyna\Component\Commerce\Cart\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSale;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Cart\Model\CartAdjustmentInterface;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Model\CartItemInterface;

/**
 * Class Cart
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Cart extends AbstractSale implements CartInterface
{
    /**
     * @var \DateTime
     */
    protected $expiresAt;


    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return 'Cart #' . $this->getId();
    }

    /**
     * @inheritdoc
     */
    public function setInvoiceAddress(AddressInterface $address)
    {
        if (!$address instanceof CustomerAddressInterface) {
            throw new InvalidArgumentException('Unexpected address type.');
        }

        $this->invoiceAddress = $address;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryAddress(AddressInterface $address = null)
    {
        if (null !== $address && !($address instanceof CustomerAddressInterface)) {
            throw new InvalidArgumentException('Unexpected address type.');
        }

        $this->deliveryAddress = $address;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasItem(SaleItemInterface $item)
    {
        if (!$item instanceof CartItemInterface) {
            throw new InvalidArgumentException("Expected instance of CartItemInterface.");
        }

        return $this->items->contains($item);
    }

    /**
     * @inheritdoc
     */
    public function addItem(SaleItemInterface $item)
    {
        if (!$item instanceof CartItemInterface) {
            throw new InvalidArgumentException("Expected instance of CartItemInterface.");
        }

        if (!$this->hasItem($item)) {
            $item->setCart($this);
            $this->items->add($item);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeItem(SaleItemInterface $item)
    {
        if (!$item instanceof CartItemInterface) {
            throw new InvalidArgumentException("Expected instance of CartItemInterface.");
        }

        if ($this->hasItem($item)) {
            $item->setCart(null);
            $this->items->removeElement($item);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasAdjustment(AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof CartAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of CartAdjustmentInterface.");
        }

        return $this->adjustments->contains($adjustment);
    }

    /**
     * @inheritdoc
     */
    public function addAdjustment(AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof CartAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of CartAdjustmentInterface.");
        }

        if (!$this->hasAdjustment($adjustment)) {
            $adjustment->setCart($this);
            $this->adjustments->add($adjustment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAdjustment(AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof CartAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of CartAdjustmentInterface.");
        }

        if ($this->hasAdjustment($adjustment)) {
            $adjustment->setCart(null);
            $this->adjustments->removeElement($adjustment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @inheritdoc
     */
    public function setExpiresAt(\DateTime $expiresAt = null)
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }
}
