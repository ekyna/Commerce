<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Order\Model\AdjustmentInterface;

/**
 * Class AbstractAdjustment
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAdjustment implements AdjustmentInterface
{
    /**
     * @var string
     */
    protected $designation;

    /**
     * @var float
     */
    protected $amount;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var int
     */
    protected $position;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->mode = AdjustmentInterface::MODE_FLAT;
        $this->position = 0;
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
}
