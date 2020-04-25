<?php

namespace Ekyna\Component\Commerce\Subject\Model;

use Ekyna\Component\Commerce\Pricing\Model\TaxableTrait;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;

/**
 * Trait SubjectRelativeTrait
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @see     SubjectRelativeInterface
 */
trait SubjectRelativeTrait
{
    use TaxableTrait;

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
     * @var SubjectIdentity
     */
    protected $subjectIdentity;


    /**
     * Initializes the subject relative.
     */
    protected function initializeSubjectRelative(): void
    {
        $this->netPrice = 0.;
        $this->weight = 0.;
        $this->subjectIdentity = new SubjectIdentity();
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

    /**
     * Returns whether or not the subject identity is set.
     *
     * @see SubjectIdentity::hasIdentity()
     *
     * @return bool
     */
    public function hasSubjectIdentity(): bool
    {
        return $this->subjectIdentity->hasIdentity();
    }

    /**
     * Returns the subject identity.
     *
     * @return SubjectIdentity
     *
     * @internal
     */
    public function getSubjectIdentity(): SubjectIdentity
    {
        return $this->subjectIdentity;
    }

    /**
     * Sets the subject identity.
     *
     * @param SubjectIdentity $identity
     *
     * @return $this|SubjectRelativeInterface
     *
     * @internal
     */
    public function setSubjectIdentity(SubjectIdentity $identity): SubjectRelativeInterface
    {
        $this->subjectIdentity = $identity;

        return $this;
    }

    /**
     * Clears the subject identity.
     *
     * @return $this|SubjectRelativeInterface
     *
     * @internal
     */
    public function clearSubjectIdentity(): SubjectRelativeInterface
    {
        $this->subjectIdentity->clear();

        return $this;
    }
}
