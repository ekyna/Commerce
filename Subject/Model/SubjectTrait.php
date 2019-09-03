<?php

namespace Ekyna\Component\Commerce\Subject\Model;

use Ekyna\Component\Commerce\Pricing\Model\TaxableTrait;

/**
 * Trait SubjectTrait
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait SubjectTrait
{
    use TaxableTrait;

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
    protected $netPrice = 0;


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
     * @return $this|SubjectInterface
     */
    public function setDesignation(string $designation = null): SubjectInterface
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
     * @return $this|SubjectInterface
     */
    public function setReference(string $reference = null): SubjectInterface
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Returns the net price.
     *
     * @return float
     */
    public function getNetPrice(): ?float
    {
        return $this->netPrice;
    }

    /**
     * Sets the net price.
     *
     * @param float $price
     *
     * @return $this|SubjectInterface
     */
    public function setNetPrice(float $price = null): SubjectInterface
    {
        $this->netPrice = $price;

        return $this;
    }
}
