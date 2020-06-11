<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Resource\Model\TranslationInterface;

/**
 * Interface MentionTranslationInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface MentionTranslationInterface extends TranslationInterface
{
    /**
     * Returns the content.
     *
     * @return string
     */
    public function getContent(): ?string;

    /**
     * Sets the content.
     *
     * @param string $content
     *
     * @return MentionTranslationInterface
     */
    public function setContent(string $content): MentionTranslationInterface;
}
