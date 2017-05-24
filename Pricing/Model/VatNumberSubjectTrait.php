<?php

namespace Ekyna\Component\Commerce\Pricing\Model;

/**
 * Trait VatNumberSubjectTrait
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait VatNumberSubjectTrait
{
    /**
     * @var string
     */
    protected $vatNumber;

    /**
     * @var array
     */
    protected $vatDetails = [];

    /**
     * @var bool
     */
    protected $vatValid = false;


    /**
     * Returns the vat number.
     *
     * @return string
     */
    public function getVatNumber()
    {
        return $this->vatNumber;
    }

    /**
     * Sets the vat number.
     *
     * @param string $vatNumber
     *
     * @return $this
     */
    public function setVatNumber($vatNumber)
    {
        $this->vatNumber = $vatNumber;

        return $this;
    }

    /**
     * Returns the vat details.
     *
     * @return array
     */
    public function getVatDetails()
    {
        return $this->vatDetails;
    }

    /**
     * Sets the vat details.
     *
     * @param array $details
     *
     * @return $this
     */
    public function setVatDetails(array $details = null)
    {
        $this->vatDetails = $details;

        return $this;
    }

    /**
     * Returns the vat valid.
     *
     * @return bool
     */
    public function isVatValid()
    {
        return $this->vatValid;
    }

    /**
     * Sets whether the vat number is valid.
     *
     * @param bool $valid
     *
     * @return $this
     */
    public function setVatValid($valid)
    {
        $this->vatValid = (bool)$valid;

        return $this;
    }

    /**
     * Returns whether the subject is a business one.
     *
     * @return bool
     */
    public function isBusiness()
    {
        return $this->vatValid && !empty($this->vatNumber);
    }
}
