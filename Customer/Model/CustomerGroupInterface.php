<?php

namespace Ekyna\Component\Commerce\Customer\Model;

/**
 * Interface GroupInterface
 * @package Ekyna\Component\Commerce\Customer\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerGroupInterface
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
     * @return $this|CustomerGroupInterface
     */
    public function setName($name);

    /**
     * Returns whether this is the default customer group or not.
     *
     * @return bool
     */
    public function getDefault();

    /**
     * Sets whether this is the default customer group or not.
     *
     * @param bool $default
     *
     * @return $this|CustomerGroupInterface
     */
    public function setDefault($default);
}
