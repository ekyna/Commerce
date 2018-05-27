<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Calculator\Amount;
use Ekyna\Component\Commerce\Common\Calculator\Margin;
use Ekyna\Component\Commerce\Common\Model\AdjustableTrait;
use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemAdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Pricing\Model\TaxableTrait;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeTrait;
use Ekyna\Component\Resource\Model\SortableTrait;

/**
 * Class AbstractSaleItem
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleItem implements SaleItemInterface
{
    use AdjustableTrait,
        SubjectRelativeTrait,
        TaxableTrait,
        SortableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var SaleItemInterface
     */
    protected $parent;

    /**
     * @var ArrayCollection|SaleItemInterface[]
     */
    protected $children;

    /**
     * @var string
     */
    protected $designation;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $reference;

    /**
     * @var float
     */
    protected $netPrice = 0;

    /**
     * @var float
     */
    protected $weight = 0;

    /**
     * @var float
     */
    protected $quantity = 1;

    /**
     * @var bool
     */
    protected $compound = false;

    /**
     * @var bool
     */
    protected $immutable = false;

    /**
     * @var bool
     */
    protected $configurable = false;

    /**
     * @var bool
     */
    protected $private = false;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var Amount
     */
    private $result;

    /**
     * @var Margin
     */
    private $margin;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->initializeAdjustments();
        $this->initializeSubjectIdentity();

        $this->children = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getDesignation();
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
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @inheritdoc
     */
    public function setParent(SaleItemInterface $parent = null)
    {
        $parent && $this->assertItemClass($parent);

        if ($parent !== $this->parent) {
            if ($previous = $this->parent) {
                $this->parent = null;
                $previous->removeChild($this);
            }

            if ($this->parent = $parent) {
                $this->parent->addChild($this);
            }
        }

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
     * @inheritDoc
     */
    public function createChild()
    {
        $child = new static();

        $this->addChild($child);

        return $child;
    }

    /**
     * @inheritdoc
     */
    public function hasChild(SaleItemInterface $child)
    {
        $this->assertItemClass($child);

        return $this->children->contains($child);
    }

    /**
     * @inheritdoc
     */
    public function addChild(SaleItemInterface $child)
    {
        $this->assertItemClass($child);

        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeChild(SaleItemInterface $child)
    {
        $this->assertItemClass($child);

        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            $child->setParent(null);
        }

        return $this;
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
    public function hasAdjustment(AdjustmentInterface $adjustment)
    {
        $this->assertItemAdjustmentClass($adjustment);

        return $this->adjustments->contains($adjustment);
    }

    /**
     * @inheritdoc
     */
    public function addAdjustment(AdjustmentInterface $adjustment)
    {
        $this->assertItemAdjustmentClass($adjustment);

        /** @var SaleItemAdjustmentInterface $adjustment*/
        if (!$this->adjustments->contains($adjustment)) {
            $this->adjustments->add($adjustment);
            $adjustment->setItem($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAdjustment(AdjustmentInterface $adjustment)
    {
        $this->assertItemAdjustmentClass($adjustment);

        /** @var AbstractSaleItemAdjustment $adjustment*/
        if ($this->adjustments->contains($adjustment)) {
            $this->adjustments->removeElement($adjustment);
            $adjustment->setItem(null);
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        $this->description = $description;

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
        $this->netPrice = (float)$netPrice;

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
        $this->weight = (float)$weight;

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
    public function isCompound()
    {
        return $this->compound;
    }

    /**
     * @inheritdoc
     */
    public function setCompound($compound)
    {
        $this->compound = (bool)$compound;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isImmutable()
    {
        return $this->immutable;
    }

    /**
     * @inheritdoc
     */
    public function setImmutable($immutable)
    {
        $this->immutable = (bool)$immutable;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isConfigurable()
    {
        return $this->configurable;
    }

    /**
     * @inheritdoc
     */
    public function setConfigurable($configurable)
    {
        $this->configurable = (bool)$configurable;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isPrivate()
    {
        return $this->private;
    }

    /**
     * @inheritdoc
     */
    public function setPrivate($private)
    {
        $this->private = (bool)$private;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasPrivateChildren()
    {
        foreach ($this->children as $child) {
            if ($child->isPrivate()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function hasPublicChildren()
    {
        foreach ($this->children as $child) {
            if (!$child->isPrivate()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function hasData($key = null)
    {
        if (!empty($key)) {
            return array_key_exists($key, (array)$this->data) && !empty($this->data[$key]);
        }

        return !empty($this->data);
    }

    /**
     * @inheritdoc
     */
    public function getData($key = null)
    {
        if (!empty($key)) {
            if (array_key_exists($key, (array)$this->data)) {
                return $this->data[$key];
            }

            return null;
        }

        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function setData($keyOrData, $data = null)
    {
        if (is_array($keyOrData) && null === $data) {
            $this->data = $keyOrData;
        } elseif (is_string($keyOrData) && !empty($keyOrData)) {
            $this->data[$keyOrData] = $data;
        } else {
            throw new InvalidArgumentException(sprintf("Bad usage of %s::setData", static::class));
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function unsetData($key)
    {
        if (is_string($key) && !empty($key)) {
            if (array_key_exists($key, (array)$this->data)) {
                unset($this->data[$key]);
            }
        } else {
            throw new InvalidArgumentException('Expected key as string.');
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getParentsQuantity()
    {
        $modifier = 1;

        $parent = $this;
        while (null !== $parent = $parent->getParent()) {
            $modifier *= $parent->getQuantity();
        }

        return $modifier;
    }

    /**
     * @inheritdoc
     */
    public function getTotalQuantity()
    {
        return $this->getQuantity() * $this->getParentsQuantity();
    }

    /**
     * @inheritdoc
     */
    public function clearResult()
    {
        foreach ($this->children as $child) {
            $child->clearResult();
        }

        $this->result = null;
        $this->margin = null;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setResult(Amount $result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @inheritdoc
     */
    public function setMargin(Margin $margin)
    {
        $this->margin = $margin;
    }

    /**
     * @inheritdoc
     */
    public function getMargin()
    {
        return $this->margin;
    }

    /**
     * @inheritdoc
     */
    public function isLast()
    {
        if (null !== $this->parent) {
            return $this->position === $this->parent->getChildren()->last()->getPosition();
        }

        return $this->position === $this->getSale()->getItems()->last()->getPosition();
    }

    /**
     * @inheritDoc
     */
    public function getHash($encode = true)
    {
        $data = [
            'r' => $this->reference,
        ];

        if ($this->hasSubjectIdentity()) {
            $data['p'] = $this->subjectIdentity->getProvider();
            $data['i'] = $this->subjectIdentity->getIdentifier();
        }

        if (!empty($this->data)) {
            $data['d'] = $this->data;
        }

        if (null === $this->parent) {
            $data['q'] = floatval($this->quantity); // TODO Packaging format
        }

        if (0 < $this->children->count()) {
            $data['c'] = [];
            foreach ($this->children as $child) {
                $data['c'][] = $child->getHash(false);
            }
        }

        if ($encode) {
            return md5(json_encode($data));
        }

        return $data;
    }

    /**
     * @inheritDoc
     *
     * @see https://github.com/Atlantic18/DoctrineExtensions/issues/1726
     */
    public function compareTo($other)
    {
        if ($other instanceof SaleItemInterface) {
            return $this->position - $other->getPosition();
        }

        return 0;
    }

    /**
     * Asserts that the given sale is an instance of the expected class.
     *
     * @param SaleInterface $sale
     */
    abstract protected function assertSaleClass(SaleInterface $sale);

    /**
     * Asserts that the given sale item is an instance of the expected class.
     *
     * @param SaleItemInterface $child
     */
    abstract protected function assertItemClass(SaleItemInterface $child);

    /**
     * Asserts that the given adjustment is an instance of the expected class.
     *
     * @param AdjustmentInterface $adjustment
     */
    abstract protected function assertItemAdjustmentClass(AdjustmentInterface $adjustment);
}
