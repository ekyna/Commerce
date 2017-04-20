<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Resource\Model\TranslatableInterface;

/**
 * Interface MessageInterface
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method MessageTranslationInterface translate($locale = null, $create = false)
 */
interface MessageInterface extends TranslatableInterface
{
    public function getState(): ?string;

    public function setState(?string $state): MessageInterface;

    public function getMethod(): ?MethodInterface;

    public function setMethod(?MethodInterface $method): MessageInterface;

    /**
     * Returns the content (translation alias).
     */
    public function getContent(): ?string;

    /**
     * Sets the content (translation alias).
     */
    public function setContent(?string $content): MessageInterface;
}
