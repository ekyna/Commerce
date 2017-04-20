<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Resource\Model\TranslationInterface;

/**
 * Interface MentionTranslationInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface MentionTranslationInterface extends TranslationInterface
{
    public function getContent(): ?string;

    public function setContent(?string $content): MentionTranslationInterface;
}
