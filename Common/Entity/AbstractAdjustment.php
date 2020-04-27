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
     * @var string
     */
    protected $source;


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
    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    /**
     * @inheritdoc
     */
    public function setDesignation(string $designation = null): Model\AdjustmentInterface
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType(string $type): Model\AdjustmentInterface
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @inheritdoc
     */
    public function setMode(string $mode): Model\AdjustmentInterface
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @inheritdoc
     */
    public function setAmount(float $amount): Model\AdjustmentInterface
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isImmutable(): bool
    {
        return $this->immutable;
    }

    /**
     * @inheritdoc
     */
    public function setImmutable(bool $immutable): Model\AdjustmentInterface
    {
        $this->immutable = $immutable;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * @inheritdoc
     */
    public function setSource(string $source = null): Model\AdjustmentInterface
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function equals(Model\AdjustmentInterface $adjustment): bool
    {
        // TODO unique hash (other data may vary)

        return $this->designation == $adjustment->getDesignation()
            && $this->type == $adjustment->getType()
            && $this->mode == $adjustment->getMode()
            && $this->amount == $adjustment->getAmount()
            && $this->immutable == $adjustment->isImmutable()
            && $this->source == $adjustment->getSource();
    }

    /**
     * @inheritdoc
     */
    abstract public function getAdjustable(): ?Model\AdjustableInterface;
}
