<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Resource\Model\SortableTrait;

/**
 * Class AbstractAdjustment
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAdjustment implements Model\AdjustmentInterface
{
    use SortableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $designation;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var float
     */
    protected $amount;

    /**
     * @var bool
     */
    protected $immutable;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->type = Model\AdjustmentTypes::TYPE_DISCOUNT;
        $this->mode = Model\AdjustmentModes::MODE_PERCENT;
        $this->immutable = false;
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @inheritdoc
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @inheritdoc
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

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
    public function equals(Model\AdjustmentInterface $adjustment)
    {
        // TODO unique hash (other data may vary)

        return $this->designation == $adjustment->getDesignation()
            && $this->type == $adjustment->getType()
            && $this->mode == $adjustment->getMode()
            && $this->amount == $adjustment->getAmount()
            && $this->immutable == $adjustment->isImmutable();
    }

    /**
     * @inheritdoc
     */
    abstract public function getAdjustable();
}
