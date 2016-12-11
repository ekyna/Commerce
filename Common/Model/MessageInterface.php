<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface MessageInterface
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method MessageTranslationInterface translate($locale = null, $create = false)
 */
interface MessageInterface extends ResourceInterface
{
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
     * Returns the content (translation alias).
     *
     * @return string
     */
    public function getContent();

    /**
     * Sets the content (translation alias).
     *
     * @param string $content
     *
     * @return $this|MessageInterface
     */
    public function setContent($content);
}
