<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class AbstractSaleItem
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleItem extends AbstractAdjustable implements SaleItemInterface
{
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
    protected $netPrice;

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
     * @var array
     */
    protected $subjectData;

    /**
     * @var mixed
     */
    protected $subject;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->children = new ArrayCollection();

        $this->quantity = 1;
        $this->position = 0;
        $this->subjectData = [];
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
        $this->netPrice = $netPrice;

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
    public function hasSubjectData()
    {
        return !empty($this->subjectData);
    }

    /**
     * @inheritdoc
     */
    public function getSubjectData($key = null)
    {
        if (0 < strlen($key)) {
            if (array_key_exists($key, (array)$this->subjectData)) {
                return $this->subjectData[$key];
            }

            return null;
        }

        return $this->subjectData;
    }

    /**
     * @inheritdoc
     */
    public function setSubjectData($keyOrData, $data = null)
    {
        if (is_array($keyOrData) && null === $data) {
            $this->subjectData = $keyOrData;
        } elseif (is_string($keyOrData) && 0 < strlen($keyOrData)) {
            $this->subjectData[$keyOrData] = $data;
        } else {
            throw new InvalidArgumentException(sprintf("Bad usage of %s::setSubjectData", static::class));
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function unsetSubjectData($key)
    {
        if (is_string($key) && 0 < strlen($key)) {
            if (array_key_exists($key, $this->subjectData)) {
                unset($this->subjectData[$key]);
            }
        } else {
            throw new InvalidArgumentException('Expected key as string.');
        }

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
    public function setSubject($subject = null)
    {
        $this->subject = $subject;

        return $this;
    }
}
