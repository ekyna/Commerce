<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Resource\Model\AbstractTranslatable;

/**
 * Class AbstractMessage
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\MessageTranslationInterface translate($locale = null, $create = false)
 */
abstract class AbstractMessage extends AbstractTranslatable implements Model\MessageInterface
{
    protected ?string                $state  = null;
    protected ?Model\MethodInterface $method = null;

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): Model\MessageInterface
    {
        $this->state = $state;

        return $this;
    }

    public function getMethod(): ?Model\MethodInterface
    {
        return $this->method;
    }

    public function setMethod(?Model\MethodInterface $method): Model\MessageInterface
    {
        $this->method = $method;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->translate()->getContent();
    }

    public function setContent(?string $content): Model\MessageInterface
    {
        $this->translate()->setContent($content);

        return $this;
    }
}
