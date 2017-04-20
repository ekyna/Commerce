<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Subject\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Pricing\Model\TaxableTrait;

/**
 * Trait SubjectTrait
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait SubjectTrait
{
    use TaxableTrait;

    protected ?string $designation = null;
    protected ?string $reference = null;
    protected Decimal $netPrice;

    protected function initializeSubject(): void
    {
        $this->netPrice = new Decimal(0);
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): SubjectInterface
    {
        $this->designation = $designation;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): SubjectInterface
    {
        $this->reference = $reference;

        return $this;
    }

    public function getNetPrice(): Decimal
    {
        return $this->netPrice;
    }

    public function setNetPrice(Decimal $price): SubjectInterface
    {
        $this->netPrice = $price;

        return $this;
    }
}
