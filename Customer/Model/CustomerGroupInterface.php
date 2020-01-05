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
    public function getName(): ?string;

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return $this|CustomerGroupInterface
     */
    public function setName(string $name): CustomerGroupInterface;

    /**
     * Returns whether or not this is the default customer group.
     *
     * @return bool
     */
    public function isDefault(): bool;

    /**
     * Sets whether or not this is the default customer group.
     *
     * @param bool $default
     *
     * @return $this|CustomerGroupInterface
     */
    public function setDefault(bool $default): CustomerGroupInterface;

    /**
     * Returns whether or not this group is a business one.
     *
     * @return bool
     */
    public function isBusiness(): bool;

    /**
     * Sets whether or not this group is a business one.
     *
     * @param bool $business
     *
     * @return $this|CustomerGroupInterface
     */
    public function setBusiness(bool $business): CustomerGroupInterface;

    /**
     * Returns whether or not this group is available for registration (apply).
     *
     * @return bool
     */
    public function isRegistration(): bool;

    /**
     * Sets whether or not this group is available for registration (apply).
     *
     * @param bool $registration
     *
     * @return $this|CustomerGroupInterface
     */
    public function setRegistration(bool $registration): CustomerGroupInterface;

    /**
     * Returns whether users of this group can create quotes.
     *
     * @return bool
     */
    public function isQuoteAllowed(): bool;

    /**
     * Sets whether users of this group can create quotes.
     *
     * @param bool $allowed
     *
     * @return $this|CustomerGroupInterface
     */
    public function setQuoteAllowed(bool $allowed): CustomerGroupInterface;

    /**
     * Returns whether loyalty is enabled for this group.
     *
     * @return bool
     */
    public function isLoyalty(): bool;

    /**
     * Sets whether loyalty is enabled for this group.
     *
     * @param bool $enabled
     *
     * @return $this|CustomerGroupInterface
     */
    public function setLoyalty(bool $enabled): CustomerGroupInterface;

    /**
     * Returns the vat display mode.
     *
     * @return string|null
     */
    public function getVatDisplayMode(): ?string;

    /**
     * Sets the vat display mode.
     *
     * @param string|null $mode
     *
     * @return $this|CustomerGroupInterface
     */
    public function setVatDisplayMode(string $mode = null): CustomerGroupInterface;

    /**
     * Returns the (translated) title.
     *
     * @return string|null
     */
    public function getTitle(): ?string;
}
