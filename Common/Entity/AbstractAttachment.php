<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model\AttachmentInterface;
use Ekyna\Component\Resource\Model\UploadableTrait;

/**
 * Class AbstractAttachment
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAttachment implements AttachmentInterface
{
    use UploadableTrait;

    protected ?int    $id       = null;
    protected ?string $title    = null;
    protected ?string $type     = null;
    protected bool    $internal = false;


    /**
     * Returns the string representation.
     */
    public function __toString(): string
    {
        return $this->getFilename() ?: 'New attachment';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): AttachmentInterface
    {
        $this->title = $title;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): AttachmentInterface
    {
        $this->type = $type;

        return $this;
    }

    public function isInternal(): bool
    {
        return $this->internal;
    }

    public function setInternal(bool $internal): AttachmentInterface
    {
        $this->internal = $internal;

        return $this;
    }
}
