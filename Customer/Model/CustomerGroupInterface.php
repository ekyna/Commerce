<?php

namespace Ekyna\Component\Commerce\Customer\Model;

use Ekyna\Component\Resource\Model\TranslatableInterface;

/**
 * Interface GroupInterface
 * @package Ekyna\Component\Commerce\Customer\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method CustomerGroupTranslationInterface translate($locale = null, $create = false)
 */
interface CustomerGroupInterface extends TranslatableInterface
{
    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return $this|CustomerGroupInterface
     */
    public function setName($name);

    /**
     * Returns whether or not this is the default customer group.
     *
     * @return bool
     */
    public function isDefault();

    /**
     * Sets whether or not this is the default customer group.
     *
     * @param bool $default
     *
     * @return $this|CustomerGroupInterface
     */
    public function setDefault($default);

    /**
     * Returns whether or not this group is a business one.
     *
     * @return bool
     */
    public function isBusiness();

    /**
     * Sets whether or not this group is a business one.
     *
     * @param bool $business
     *
     * @return $this|CustomerGroupInterface
     */
    public function setBusiness($business);

    /**
     * Returns whether or not this group is available for registration (apply).
     *
     * @return bool
     */
    public function isRegistration();

    /**
     * Sets whether or not this group is available for registration (apply).
     *
     * @param bool $registration
     *
     * @return $this|CustomerGroupInterface
     */
    public function setRegistration($registration);

    /**
     * Returns whether users of this group can create quotes.
     *
     * @return bool
     */
    public function isQuoteAllowed();

    /**
     * Sets whether users of this group can create quotes.
     *
     * @param bool $allowed
     *
     * @return $this|CustomerGroupInterface
     */
    public function setQuoteAllowed($allowed);

    /**
     * Returns whether the group has free shipping.
     *
     * @return bool
     */
    public function isFreeShipping();

    /**
     * Sets whether the group has free shipping.
     *
     * @param bool $free
     *
     * @return $this|CustomerGroupInterface
     */
    public function setFreeShipping($free);

    /**
     * Returns the vat display mode.
     *
     * @return string|null
     */
    public function getVatDisplayMode();

    /**
     * Sets the vat display mode.
     *
     * @param string|null $mode
     *
     * @return $this|CustomerGroupInterface
     */
    public function setVatDisplayMode($mode);

    /**
     * Returns the (translated) title.
     *
     * @return string
     */
    public function getTitle();
}
