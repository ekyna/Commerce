<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\AdjustableTrait;
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
     * @var array
     */
    protected $data = [];


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
    public function getSoldQuantity()
    {
        return $this->getTotalQuantity();
    }

    /**
     * @inheritDoc
     */
    public function compareTo($other)
    {
        // TODO
        /** @see https://github.com/Atlantic18/DoctrineExtensions/issues/1726 */
        // return 1 if this object is considered greater than the compare value
        // return -1 if this object is considered less than the compare value
        // return 0 if this object is considered equal to the compare value

        return 0;
    }
}
