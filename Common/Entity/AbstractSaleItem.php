<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxableTrait;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeTrait;
use Ekyna\Component\Resource\Model\SortableTrait;

/**
 * Class AbstractSaleItem
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleItem extends AbstractAdjustable implements SaleItemInterface
{
    use SubjectRelativeTrait,
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
    protected $immutable = false;

    /**
     * @var bool
     */
    protected $configurable = false;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

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
    public function getTotalQuantity()
    {
        $quantity = $this->getQuantity();

        $parent = $this;
        while (null !== $parent = $parent->getParent()) {
            $quantity *= $parent->getQuantity();
        }

        return $quantity;
    }
}
