<?php

namespace Ekyna\Component\Commerce\Pricing\Model;

/**
 * Interface CurrencyInterface
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CurrencyInterface
{
    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId();

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
}
