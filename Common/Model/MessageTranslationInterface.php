<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Resource\Model\TranslationInterface;

/**
 * Interface MessageTranslationInterface
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface MessageTranslationInterface extends TranslationInterface
{
    public function getContent(): ?string;

    public function setContent(?string $content): MessageTranslationInterface;
}
