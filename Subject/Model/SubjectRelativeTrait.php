<?php

namespace Ekyna\Component\Commerce\Subject\Model;

use Ekyna\Component\Commerce\Pricing\Model\TaxableTrait;

/**
 * Trait SubjectRelativeTrait
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @see     SubjectRelativeInterface
 */
trait SubjectRelativeTrait
{
    use SubjectReferenceTrait,
        TaxableTrait;

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
     * Initializes the subject relative.
     */
    protected function initializeSubjectRelative(): void
    {
        $this->netPrice = 0.;
        $this->weight = 0.;

        $this->initializeSubjectIdentity();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the designation.
     *
     * @return string
     */
    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    /**
     * Sets the designation.
     *
     * @param string $designation
     *
     * @return $this|SubjectRelativeInterface
     */
    public function setDesignation(string $designation): SubjectRelativeInterface
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * Returns the reference.
     *
     * @return string
     */
    public function getReference(): ?string
    {
        return $this->reference;
    }

    /**
     * Sets the reference.
     *
     * @param string $reference
     *
     * @return $this|SubjectRelativeInterface
     */
    public function setReference(string $reference): SubjectRelativeInterface
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Returns the net price.
     *
     * @return float
     */
    public function getNetPrice(): float
    {
        return $this->netPrice;
    }

    /**
     * Sets the net price.
     *
     * @param float $price
     *
     * @return $this|SubjectRelativeInterface
     */
    public function setNetPrice(float $price): SubjectRelativeInterface
    {
        $this->netPrice = $price;

        return $this;
    }

    /**
     * Returns the weight (kilograms).
     *
     * @return float
     */
    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * Sets the weight (kilograms).
     *
     * @param float $weight
     *
     * @return $this|SubjectRelativeInterface
     */
    public function setWeight(float $weight): SubjectRelativeInterface
    {
        $this->weight = (float)$weight;

        return $this;
    }
}
