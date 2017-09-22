<?php

namespace Ekyna\Component\Commerce\Customer\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface GroupInterface
 * @package Ekyna\Component\Commerce\Customer\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerGroupInterface extends ResourceInterface
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
}
