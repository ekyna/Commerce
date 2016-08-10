<?php

namespace Ekyna\Component\Commerce\Product\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface BundleSlotInterface
 * @package Ekyna\Component\Commerce\Product\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface BundleSlotInterface extends ResourceInterface
{
    /**
     * Returns the bundle.
     *
     * @return ProductInterface
     */
    public function getBundle();

    /**
     * Sets the bundle.
     *
     * @param ProductInterface $bundle
     *
     * @return $this|BundleSlotInterface
     */
    public function setBundle(ProductInterface $bundle = null);

    /**
     * Returns the choices.
     *
     * @return ArrayCollection|BundleChoiceInterface[]
     */
    public function getChoices();

    /**
     * Returns whether the slot has the choice or not.
     *
     * @param BundleChoiceInterface $choice
     *
     * @return bool
     */
    public function hasChoice(BundleChoiceInterface $choice);

    /**
     * Adds the choice.
     *
     * @param BundleChoiceInterface $choice
     *
     * @return $this|BundleSlotInterface
     */
    public function addChoice(BundleChoiceInterface $choice);

    /**
     * Removes the choice.
     *
     * @param BundleChoiceInterface $choice
     *
     * @return $this|BundleSlotInterface
     */
    public function removeChoice(BundleChoiceInterface $choice);

    /**
     * Sets the choices.
     *
     * @param ArrayCollection|BundleChoiceInterface[] $choices
     *
     * @return $this|BundleSlotInterface
     * @internal
     */
    public function setChoices($choices);

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
     * @return $this|BundleSlotInterface
     */
    public function setPosition($position);
}
