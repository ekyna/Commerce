<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Resource\Model\SortableInterface;
use Ekyna\Component\Resource\Model\TranslatableInterface;

/**
 * Interface MentionInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method MentionTranslationInterface translate($locale = null, $create = false)
 */
interface MentionInterface extends TranslatableInterface, SortableInterface
{
    /**
     * Returns the document types.
     *
     * @return string[]
     */
    public function getDocumentTypes(): array;

    /**
     * Adds the document type.
     *
     * @param string $type
     *
     * @return MentionInterface
     */
    public function addDocumentType(string $type): MentionInterface;

    /**
     * Sets the document types.
     *
     * @param string[] $types
     *
     * @return MentionInterface
     */
    public function setDocumentTypes(array $types): MentionInterface;

    /**
     * Returns the (translated) content.
     *
     * @return string
     */
    public function getContent();
}
