<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Pricing\Model;

/**
 * Trait VatNumberSubjectTrait
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait VatNumberSubjectTrait
{
    protected ?string $vatNumber = null;
    protected array   $vatDetails = [];
    protected bool    $vatValid   = false;


    /**
     * Returns the vat number.
     */
    public function getVatNumber(): ?string
    {
        return $this->vatNumber;
    }

    /**
     * Sets the vat number.
     *
     * @return $this|VatNumberSubjectInterface
     */
    public function setVatNumber(?string $vatNumber): VatNumberSubjectInterface
    {
        $this->vatNumber = $vatNumber;

        return $this;
    }

    /**
     * Returns the vat details.
     */
    public function getVatDetails(): array
    {
        return $this->vatDetails;
    }

    /**
     * Sets the vat details.
     *
     * @return $this|VatNumberSubjectInterface
     */
    public function setVatDetails(array $details): VatNumberSubjectInterface
    {
        $this->vatDetails = $details;

        return $this;
    }

    /**
     * Returns the vat valid.
     */
    public function isVatValid(): bool
    {
        return $this->vatValid;
    }

    /**
     * Sets whether the vat number is valid.
     *
     * @return $this|VatNumberSubjectInterface
     */
    public function setVatValid(bool $valid): VatNumberSubjectInterface
    {
        $this->vatValid = $valid;

        return $this;
    }

    /**
     * Returns whether the subject is a business one.
     */
    public function isBusiness(): bool
    {
        return $this->vatValid && !empty($this->vatNumber);
    }
}
