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
     * @var string
     */
    private $source;

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
     * @param string $source
     */
    public function __construct(
        string $mode,
        string $designation,
        float $amount,
        string $source,
        bool $immutable = true
    ) {
        $this->mode = $mode;
        $this->designation = $designation;
        $this->amount = $amount;
        $this->source = $source;
        $this->immutable = $immutable;
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
     * @inheritDoc
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @inheritdoc
     */
    public function isImmutable()
    {
        return $this->immutable;
    }
}
