<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Subject\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\Units;
use Ekyna\Component\Commerce\Pricing\Model\TaxableTrait;
use Ekyna\Component\Resource\Model\ResourceTrait;

/**
 * Trait SubjectRelativeTrait
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @see     SubjectRelativeInterface
 */
trait SubjectRelativeTrait
{
    use ResourceTrait;
    use SubjectReferenceTrait;
    use TaxableTrait;

    protected ?string $designation = null;
    protected ?string $reference   = null;
    protected Decimal $netPrice;
    protected Decimal $weight;
    protected string  $unit        = Units::PIECE;

    protected function initializeSubjectRelative(): void
    {
        $this->initializeSubjectIdentity();

        $this->netPrice = new Decimal(0);
        $this->weight = new Decimal(0);
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    /**
     * @return $this|SubjectRelativeInterface
     */
    public function setDesignation(string $designation): SubjectRelativeInterface
    {
        $this->designation = $designation;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    /**
     * @return $this|SubjectRelativeInterface
     */
    public function setReference(string $reference): SubjectRelativeInterface
    {
        $this->reference = $reference;

        return $this;
    }

    public function getNetPrice(): Decimal
    {
        return $this->netPrice;
    }

    /**
     * @return $this|SubjectRelativeInterface
     */
    public function setNetPrice(Decimal $price): SubjectRelativeInterface
    {
        $this->netPrice = $price;

        return $this;
    }

    /**
     * Returns the weight (kilograms).
     */
    public function getWeight(): Decimal
    {
        return $this->weight;
    }

    /**
     * Sets the weight (kilograms).
     *
     * @return $this|SubjectRelativeInterface
     */
    public function setWeight(Decimal $weight): SubjectRelativeInterface
    {
        $this->weight = $weight;

        return $this;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    /**
     * @return $this|SubjectRelativeInterface
     */
    public function setUnit(string $unit): SubjectRelativeInterface
    {
        $this->unit = $unit;

        return $this;
    }
}
