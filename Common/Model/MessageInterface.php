<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface MessageInterface
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface MessageInterface
{
    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId();

    /**
     * Returns the state.
     *
     * @return string
     */
    public function getState();

    /**
     * Sets the state.
     *
     * @param string $state
     *
     * @return $this|MessageInterface
     */
    public function setState($state);

    /**
     * Returns the method.
     *
     * @return MethodInterface
     */
    public function getMethod();

    /**
     * Sets the method.
     *
     * @param MethodInterface $method
     *
     * @return $this|MessageInterface
     */
    public function setMethod(MethodInterface $method);

    /**
     * Returns the content.
     *
     * @return string
     */
    public function getContent();
}
