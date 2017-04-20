<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Subject\Model;

use Decimal\Decimal;
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
     */
    public static function getProviderName(): string;

    /**
     * Returns the subject identifier.
     */
    public function getIdentifier(): int;

    public function getDesignation(): ?string;

    /**
     * @return $this|SubjectInterface
     */
    public function setDesignation(?string $designation): SubjectInterface;

    public function getReference(): ?string;

    /**
     * @return $this|SubjectInterface
     */
    public function setReference(?string $reference): SubjectInterface;

    public function getNetPrice(): Decimal;

    /**
     * @return $this|SubjectInterface
     */
    public function setNetPrice(Decimal $netPrice): SubjectInterface;
}
