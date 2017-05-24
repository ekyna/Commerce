<?php

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
     *
     * @return string
     */
    public function getVatNumber();

    /**
     * Sets the vat number.
     *
     * @param string $vatNumber
     *
     * @return $this|VatNumberSubjectInterface
     */
    public function setVatNumber($vatNumber);

    /**
     * Returns the vat details.
     *
     * @return array
     */
    public function getVatDetails();

    /**
     * Sets the vat details.
     *
     * @param array $details
     *
     * @return $this|VatNumberSubjectInterface
     */
    public function setVatDetails(array $details);

    /**
     * Returns the vat valid.
     *
     * @return bool
     */
    public function isVatValid();

    /**
     * Sets whether the vat number is valid.
     *
     * @param bool $valid
     *
     * @return $this|VatNumberSubjectInterface
     */
    public function setVatValid($valid);

    /**
     * Returns whether the customer is a business one.
     *
     * @return bool
     */
    public function isBusiness();
}
