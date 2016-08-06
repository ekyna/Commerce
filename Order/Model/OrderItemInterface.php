<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\EntityInterface;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Interface OrderItemInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderItemInterface extends EntityInterface
{
    /**
     * Returns the order.
     *
     * @return OrderInterface
     */
    public function getOrder();

    /**
     * Sets the order.
     *
     * @param OrderInterface $order
     * @return $this|OrderItemInterface
     */
    public function setOrder(OrderInterface $order = null);

    /**
     * Returns the parent.
     *
     * @return OrderItemInterface
     */
    public function getParent();

    /**
     * Sets the parent.
     *
     * @param OrderItemInterface $parent
     * @return $this|OrderItemInterface
     */
    public function setParent(OrderItemInterface $parent = null);

    /**
     * Returns whether the item has children or not.
     *
     * @return bool
     */
    public function hasChildren();

    /**
     * Returns the children items.
     *
     * @return ArrayCollection|OrderItemInterface[]
     */
    public function getChildren();

    /**
     * Adds the child item.
     *
     * @param OrderItemInterface $item
     * @return $this|OrderItemInterface
     */
    public function addChild(OrderItemInterface $item);

    /**
     * Removes the child item.
     *
     * @param OrderItemInterface $item
     * @return $this|OrderItemInterface
     */
    public function removeChild(OrderItemInterface $item);

    /**
     * Sets the children items.
     *
     * @param ArrayCollection|OrderItemInterface[] $children
     * @return $this|OrderItemInterface
     */
    public function setChildren(ArrayCollection $children);

    /**
     * Returns the adjustments.
     *
     * @return ArrayCollection|OrderItemAdjustmentInterface[]
     */
    public function getAdjustments();

    /**
     * Adds the adjustment.
     *
     * @param OrderItemAdjustmentInterface $adjustment
     * @return $this|OrderItemInterface
     */
    public function addAdjustment(OrderItemAdjustmentInterface $adjustment);

    /**
     * Removes the adjustment.
     *
     * @param OrderItemAdjustmentInterface $adjustment
     * @return $this|OrderItemInterface
     */
    public function removeAdjustment(OrderItemAdjustmentInterface $adjustment);

    /**
     * Sets the adjustments.
     *
     * @param ArrayCollection|OrderItemAdjustmentInterface[] $adjustments
     * @return $this|OrderItemInterface
     */
    public function setAdjustments(ArrayCollection $adjustments);

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
     * @return $this|OrderItemInterface
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
     * @return $this|OrderItemInterface
     */
    public function setReference($reference);

    /**
     * Returns the netPrice.
     *
     * @return float
     */
    public function getNetPrice();

    /**
     * Sets the netPrice.
     *
     * @param float $netPrice
     * @return $this|OrderItemInterface
     */
    public function setNetPrice($netPrice);

    /**
     * Returns the taxName.
     *
     * @return string
     */
    public function getTaxName();

    /**
     * Sets the taxName.
     *
     * @param string $taxName
     * @return $this|OrderItemInterface
     */
    public function setTaxName($taxName);

    /**
     * Returns the taxRate.
     *
     * @return float
     */
    public function getTaxRate();

    /**
     * Sets the taxRate.
     *
     * @param float $taxRate
     * @return $this|OrderItemInterface
     */
    public function setTaxRate($taxRate);

    /**
     * Returns the weight.
     *
     * @return float
     */
    public function getWeight();

    /**
     * Sets the weight.
     *
     * @param float $weight
     * @return $this|OrderItemInterface
     */
    public function setWeight($weight);

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
     * @return $this|OrderItemInterface
     */
    public function setQuantity($quantity);

    /**
     * Returns the position.
     *
     * @return int
     */
    public function getPosition();

    /**
     * Sets the position.
     *
     * @param int $position
     * @return $this|OrderItemInterface
     */
    public function setPosition($position);

    /**
     * Returns whether the subject identity is defined or not.
     *
     * @return bool
     */
    public function hasSubjectIdentity();

    /**
     * Returns the subject identity.
     *
     * @return SubjectIdentity
     */
    public function getSubjectIdentity();

    /**
     * Sets the subject identity.
     *
     * @param SubjectIdentity $subjectIdentity
     *
     * @return $this|OrderItemInterface
     */
    public function setSubjectIdentity(SubjectIdentity $subjectIdentity);

    /**
     * Returns the subject.
     *
     * @return SubjectInterface
     */
    public function getSubject();

    /**
     * Sets the subject.
     *
     * @param SubjectInterface $subject
     * @return $this|OrderItemInterface
     */
    public function setSubject(SubjectInterface $subject = null);
}
