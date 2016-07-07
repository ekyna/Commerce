<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemAdjustmentInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Class OrderItem
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItem implements OrderItemInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var OrderInterface
     */
    protected $order;

    /**
     * @var OrderItemInterface
     */
    protected $parent;

    /**
     * @var ArrayCollection|OrderItemInterface[]
     */
    protected $children;

    /**
     * @var ArrayCollection|OrderItemAdjustmentInterface[]
     */
    protected $adjustments;

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
    protected $netPrice;

    /**
     * @var string
     */
    protected $taxName;

    /**
     * @var float
     */
    protected $taxRate;

    /**
     * @var float
     */
    protected $weight;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var SubjectIdentity
     */
    protected $subjectIdentity;

    /**
     * @var SubjectInterface
     */
    protected $subject;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->adjustments = new ArrayCollection();

        $this->quantity = 1;
        $this->position = 0;

        $this->subjectIdentity = new SubjectIdentity();
    }

    /**
     * Returns the id.
     *
     * @return int
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
    public function setOrder(OrderInterface $order = null)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @inheritdoc
     * @internal
     */
    public function setParent(OrderItemInterface $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasChildren()
    {
        return 0 < $this->children->count();
    }

    /**
     * @inheritdoc
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @inheritdoc
     */
    public function addChild(OrderItemInterface $item)
    {
        if (!$this->children->contains($item)) {
            $item->setParent($this);
            $this->children->add($item);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeChild(OrderItemInterface $item)
    {
        if (!$this->children->contains($item)) {
            $item->setParent(null);
            $this->children->removeElement($item);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setChildren(ArrayCollection $children)
    {
        // TODO clear / new / add all
        $this->children = $children;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAdjustments()
    {
        return $this->adjustments;
    }

    /**
     * @inheritdoc
     */
    public function addAdjustment(OrderItemAdjustmentInterface $adjustment)
    {
        if (!$this->adjustments->contains($adjustment)) {
            $adjustment->setItem($this);
            $this->adjustments->add($adjustment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAdjustment(OrderItemAdjustmentInterface $adjustment)
    {
        if (!$this->adjustments->contains($adjustment)) {
            $adjustment->setItem(null);
            $this->adjustments->removeElement($adjustment);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setAdjustments(ArrayCollection $adjustments)
    {
        // TODO clear / new / add all
        $this->adjustments = $adjustments;

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
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * @inheritdoc
     */
    public function setNetPrice($netPrice)
    {
        $this->netPrice = $netPrice;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTaxName()
    {
        return $this->taxName;
    }

    /**
     * @inheritdoc
     */
    public function setTaxName($taxName)
    {
        $this->taxName = $taxName;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * @inheritdoc
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @inheritdoc
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

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
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasSubjectIdentity()
    {
        return $this->subjectIdentity->isDefined();
    }

    /**
     * @inheritdoc
     */
    public function getSubjectIdentity()
    {
        return $this->subjectIdentity;
    }

    /**
     * @inheritdoc
     */
    public function setSubjectIdentity(SubjectIdentity $subjectIdentity)
    {
        $this->subjectIdentity = $subjectIdentity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @inheritdoc
     */
    public function setSubject(SubjectInterface $subject = null)
    {
        $this->subject = $subject;

        return $this;
    }
}
