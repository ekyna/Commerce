<?php

namespace Ekyna\Component\Commerce\Subject\Model;

use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface SubjectInterface
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SubjectInterface extends ResourceInterface, TaxableInterface
{
    /**
     * Returns the subject provider name.
     *
     * @return string
     */
    public static function getProviderName(): string;

    /**
     * Returns the subject identifier.
     *
     * @return int|string
     */
    public function getIdentifier();

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
     * @return $this|SubjectInterface
     */
    public function setDesignation(string $designation = null): SubjectInterface;

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
     * @return $this|SubjectInterface
     */
    public function setReference(string $reference = null): SubjectInterface;

    /**
     * Returns the net price.
     *
     * @return float
     */
    public function getNetPrice(): ?float;

    /**
     * Sets the net price.
     *
     * @param float $netPrice
     *
     * @return $this|SubjectInterface
     */
    public function setNetPrice(float $netPrice = null): SubjectInterface;
}
