<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Class AdjustmentData
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AdjustmentData implements AdjustmentDataInterface
{
    /**
     * @var string
     */
    private $mode;

    /**
     * @var string
     */
    private $designation;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var bool
     */
    private $immutable;


    /**
     * Constructor.
     *
     * @param string $mode
     * @param string $designation
     * @param float  $amount
     * @param bool   $immutable
     */
    public function __construct($mode, $designation, $amount, $immutable = true)
    {
        $this->mode = $mode;
        $this->designation = $designation;
        $this->amount = (float)$amount;
        $this->immutable = (bool)$immutable;
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
    public function getDesignation()
    {
        return $this->designation;
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
    public function isImmutable()
    {
        return $this->immutable;
    }
}
