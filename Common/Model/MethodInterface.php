<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface MethodInterface
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface MethodInterface extends ResourceInterface
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
     * @return $this|MethodInterface
     */
    public function setName($name);

    /**
     * Returns whether the method is enabled or not.
     *
     * @return boolean
     */
    public function isEnabled();

    /**
     * Sets whether the method is enabled or not.
     *
     * @param boolean $enabled
     *
     * @return $this|MethodInterface
     */
    public function setEnabled($enabled);

    /**
     * Returns whether the method is available or not.
     *
     * @return boolean
     */
    public function isAvailable();

    /**
     * Sets whether the method is available or not.
     *
     * @param boolean $available
     *
     * @return $this|MethodInterface
     */
    public function setAvailable($available);
}
