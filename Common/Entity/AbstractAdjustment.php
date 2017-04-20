<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use Decimal\Decimal;
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

    protected ?int    $id          = null;
    protected ?string $designation = null;
    protected string  $type        = Model\AdjustmentTypes::TYPE_DISCOUNT;
    protected string  $mode        = Model\AdjustmentModes::MODE_PERCENT;
    protected Decimal $amount;
    protected bool    $immutable   = false;
    protected ?string $source      = null;

    public function __construct()
    {
        $this->amount = new Decimal(0);
    }

    public function __clone()
    {
        $this->id = null;
        $this->amount = clone $this->amount;
        $this->source = null;
    }

    /**
     * Returns the string representation.
     */
    public function __toString(): string
    {
        return $this->designation ?: 'New adjustment';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): Model\AdjustmentInterface
    {
        $this->designation = $designation;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Model\AdjustmentInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): Model\AdjustmentInterface
    {
        $this->mode = $mode;

        return $this;
    }

    public function getAmount(): Decimal
    {
        return $this->amount;
    }

    public function setAmount(Decimal $amount): Model\AdjustmentInterface
    {
        $this->amount = $amount;

        return $this;
    }

    public function isImmutable(): bool
    {
        return $this->immutable;
    }

    public function setImmutable(bool $immutable): Model\AdjustmentInterface
    {
        $this->immutable = $immutable;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): Model\AdjustmentInterface
    {
        $this->source = $source;

        return $this;
    }

    public function equals(Model\AdjustmentInterface $adjustment): bool
    {
        // TODO unique hash (other data may vary)

        return $this->designation == $adjustment->getDesignation()
            && $this->type === $adjustment->getType()
            && $this->mode === $adjustment->getMode()
            && $this->amount->equals($adjustment->getAmount())
            && $this->immutable === $adjustment->isImmutable()
            && $this->source == $adjustment->getSource();
    }

    abstract public function getAdjustable(): ?Model\AdjustableInterface;
}
