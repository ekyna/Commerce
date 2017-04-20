<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Pricing\Model;

/**
 * Interface VatNumberSubjectInterface
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface VatNumberSubjectInterface
{
    /**
     * Returns the vat number.
     */
    public function getVatNumber(): ?string;

    /**
     * Sets the vat number.
     *
     * @return $this|VatNumberSubjectInterface
     */
    public function setVatNumber(?string $vatNumber): VatNumberSubjectInterface;

    /**
     * Returns the vat details.
     */
    public function getVatDetails(): array;

    /**
     * Sets the vat details.
     *
     * @return $this|VatNumberSubjectInterface
     */
    public function setVatDetails(array $details): VatNumberSubjectInterface;

    /**
     * Returns the vat valid.
     */
    public function isVatValid(): bool;

    /**
     * Sets whether the vat number is valid.
     *
     * @return $this|VatNumberSubjectInterface
     */
    public function setVatValid(bool $valid): VatNumberSubjectInterface;

    /**
     * Returns whether the customer is a business one.
     *
     * @return bool
     */
    public function isBusiness(): bool;
}
