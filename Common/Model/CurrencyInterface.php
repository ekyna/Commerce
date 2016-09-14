<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface CurrencyInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CurrencyInterface extends ResourceInterface
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
     * @return $this|CurrencyInterface
     */
    public function setName($name);

    /**
     * Returns the code.
     *
     * @return string
     */
    public function getCode();

    /**
     * Sets the code.
     *
     * @param string $code
     *
     * @return $this|CurrencyInterface
     */
    public function setCode($code);

    /**
     * Returns the enabled.
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Sets the enabled.
     *
     * @param bool $enabled
     *
     * @return $this|CurrencyInterface
     */
    public function setEnabled($enabled);

    /**
     * Returns the default.
     *
     * @return boolean
     */
    public function isDefault();

    /**
     * Sets the default.
     *
     * @param boolean $default
     *
     * @return $this|CurrencyInterface
     */
    public function setDefault($default);
}
