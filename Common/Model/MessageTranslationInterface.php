<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Resource\Model\TranslationInterface;

/**
 * Interface MessageTranslationInterface
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface MessageTranslationInterface extends TranslationInterface
{
    /**
     * Returns the content.
     *
     * @return string
     */
    public function getContent();

    /**
     * Sets the content.
     *
     * @param string $content
     *
     * @return $this|MessageTranslationInterface
     */
    public function setContent($content);
}
