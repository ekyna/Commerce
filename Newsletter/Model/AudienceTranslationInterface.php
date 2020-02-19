<?php

namespace Ekyna\Component\Commerce\Newsletter\Model;

use Ekyna\Component\Resource\Model\TranslationInterface;

/**
 * Interface AudienceTranslationInterface
 * @package Ekyna\Component\Commerce\Newsletter\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface AudienceTranslationInterface extends TranslationInterface
{
    /**
     * Returns the title.
     *
     * @return string
     */
    public function getTitle(): ?string;

    /**
     * Sets the title.
     *
     * @param string $title
     *
     * @return $this|AudienceTranslationInterface
     */
    public function setTitle(string $title): AudienceTranslationInterface;

    /**
     * Returns the description.
     *
     * @return string
     */
    public function getDescription(): ?string;

    /**
     * Sets the description.
     *
     * @param string $description
     *
     * @return $this|AudienceTranslationInterface
     */
    public function setDescription(string $description = null): AudienceTranslationInterface;
}
