<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model\MentionTranslationInterface;
use Ekyna\Component\Resource\Model\AbstractTranslation;

/**
 * Class AbstractMentionTranslation
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractMentionTranslation extends AbstractTranslation implements MentionTranslationInterface
{
    protected ?string $content = null;


    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): MentionTranslationInterface
    {
        $this->content = $content;

        return $this;
    }
}
