<?php

declare(strict_types=1);

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
     * @return string[]
     */
    public function getDocumentTypes(): array;

    public function addDocumentType(string $type): MentionInterface;

    /**
     * @param string[] $types
     */
    public function setDocumentTypes(array $types): MentionInterface;

    /**
     * Returns the (translated) content.
     */
    public function getContent(): ?string;
}
