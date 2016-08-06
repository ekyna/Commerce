<?php

namespace Ekyna\Component\Commerce\Product\Model;

use Ekyna\Component\Commerce\Common\Model\EntityInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;

/**
 * Interface OptionInterface
 * @package Ekyna\Component\Commerce\Product\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OptionInterface extends EntityInterface
{
    /**
     * Returns the group.
     *
     * @return OptionGroupInterface
     */
    public function getGroup();

    /**
     * Sets the group.
     *
     * @param OptionGroupInterface $group
     *
     * @return $this|OptionInterface
     */
    public function setGroup(OptionGroupInterface $group = null);

    /**
     * Returns the designation.
     *
     * @return string
     */
    public function getDesignation();

    /**
     * Sets the designation.
     *
     * @param string $designation
     *
     * @return $this|OptionInterface
     */
    public function setDesignation($designation);

    /**
     * Returns the reference.
     *
     * @return string
     */
    public function getReference();

    /**
     * Sets the reference.
     *
     * @param string $reference
     *
     * @return $this|OptionInterface
     */
    public function setReference($reference);

    /**
     * Returns the net price.
     *
     * @return float
     */
    public function getNetPrice();

    /**
     * Sets the net price.
     *
     * @param float $netPrice
     *
     * @return $this|OptionInterface
     */
    public function setNetPrice($netPrice);

    /**
     * Returns the tax group.
     *
     * @return TaxGroupInterface
     */
    public function getTaxGroup();

    /**
     * Sets the tax group.
     *
     * @param TaxGroupInterface $group
     *
     * @return $this|OptionInterface
     */
    public function setTaxGroup(TaxGroupInterface $group);

    /**
     * Returns the position.
     *
     * @return int
     */
    public function getPosition();

    /**
     * Sets the position.
     *
     * @param int $position
     *
     * @return $this|OptionInterface
     */
    public function setPosition($position);
}
