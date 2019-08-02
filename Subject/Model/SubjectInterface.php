<?php

namespace Ekyna\Component\Commerce\Subject\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface SubjectInterface
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SubjectInterface extends ResourceInterface
{
    /**
     * Returns the subject provider name.
     *
     * @return string
     */
    public static function getProviderName();

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
    public function getDesignation();

    /**
     * Returns the reference.
     *
     * @return string
     */
    public function getReference();

    /**
     * Returns the net price.
     *
     * @return float
     */
    public function getNetPrice();

    /**
     * Returns the weight (kilograms).
     *
     * @return float
     */
    public function getWeight();

    /**
     * Returns the height (millimeters).
     *
     * @return int
     */
    public function getHeight();

    /**
     * Returns the width (millimeters).
     *
     * @return int
     */
    public function getWidth();

    /**
     * Returns the depth (millimeters).
     *
     * @return int
     */
    public function getDepth();

    /**
     * Returns the quantity unit.
     *
     * @return string
     */
    public function getUnit(); // TODO Move to StockSubjectInterface (?)

    /**
     * Returns whether all the dimensions are set.
     *
     * @return bool
     */
    public function hasDimensions();
}
