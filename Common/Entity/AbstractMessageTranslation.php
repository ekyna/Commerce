<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model\MessageTranslationInterface;
use Ekyna\Component\Resource\Model\AbstractTranslation;

/**
 * Class AbstractMethodTranslation
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractMessageTranslation extends AbstractTranslation implements MessageTranslationInterface
{
    protected ?string $content = null;


    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): MessageTranslationInterface
    {
        $this->content = $content;

        return $this;
    }
}
