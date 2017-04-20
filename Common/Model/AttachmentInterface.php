<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\UploadableInterface;

/**
 * Interface SaleAttachmentInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AttachmentInterface extends UploadableInterface, ResourceInterface
{
    public function getTitle(): ?string;

    public function setTitle(?string $title): AttachmentInterface;

    public function getType(): ?string;

    public function setType(?string $type): AttachmentInterface;

    /**
     * Returns whether the attachment is internal (not public) or not.
     */
    public function isInternal(): bool;

    /**
     * Sets whether the attachment is internal (not public) or not.
     */
    public function setInternal(bool $internal): AttachmentInterface;
}
