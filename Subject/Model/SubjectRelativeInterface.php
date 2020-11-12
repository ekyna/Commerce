<?php

namespace Ekyna\Component\Commerce\Subject\Model;

use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface SubjectRelativeInterface
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @see SubjectRelativeTrait
 */
interface SubjectRelativeInterface extends SubjectReferenceInterface, TaxableInterface, ResourceInterface
{
    /**
     * Returns the designation.
     *
     * @return string
     */
    public function getDesignation(): ?string;

    /**
     * Sets the designation.
     *
     * @param string $designation
     *
     * @return $this|SubjectRelativeInterface
     */
    public function setDesignation(string $designation): SubjectRelativeInterface;

    /**
     * Returns the reference.
     *
     * @return string
     */
    public function getReference(): ?string;

    /**
     * Sets the reference.
     *
     * @param string $reference
     *
     * @return $this|SubjectRelativeInterface
     */
    public function setReference(string $reference): SubjectRelativeInterface;

    /**
     * Returns the net price.
     *
     * @return float
     */
    public function getNetPrice(): float;

    /**
     * Sets the net price.
     *
     * @param float $price
     *
     * @return $this|SubjectRelativeInterface
     */
    public function setNetPrice(float $price): SubjectRelativeInterface;

    /**
     * Returns the weight (kilograms).
     *
     * @return float
     */
    public function getWeight(): float;

    /**
     * Sets the weight (kilograms).
     *
     * @param float $weight
     *
     * @return $this|SubjectRelativeInterface
     */
    public function setWeight(float $weight): SubjectRelativeInterface;
}
