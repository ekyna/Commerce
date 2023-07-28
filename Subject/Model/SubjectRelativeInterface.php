<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Subject\Model;

use Decimal\Decimal;
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
    public function getDesignation(): ?string;

    public function setDesignation(string $designation): SubjectRelativeInterface;

    public function getReference(): ?string;

    public function setReference(string $reference): SubjectRelativeInterface;

    public function getNetPrice(): Decimal;

    public function setNetPrice(Decimal $price): SubjectRelativeInterface;

    /**
     * Returns the weight (kilograms).
     */
    public function getWeight(): Decimal;

    /**
     * Sets the weight (kilograms).
     */
    public function setWeight(Decimal $weight): SubjectRelativeInterface;

    public function isPhysical(): bool;

    /**
     * @return $this|SubjectRelativeInterface
     */
    public function setPhysical(bool $physical): SubjectRelativeInterface;

    public function getUnit(): string;

    /**
     * @return $this|SubjectRelativeInterface
     */
    public function setUnit(string $unit): SubjectRelativeInterface;
}
