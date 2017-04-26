<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Bundle\CoreBundle\Model\UploadableInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface SaleAttachmentInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AttachmentInterface extends UploadableInterface, ResourceInterface
{
    /**
     * Returns the title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Sets the title.
     *
     * @param string $title
     *
     * @return $this|AttachmentInterface
     */
    public function setTitle($title);

    /**
     * Returns whether the attachment is internal or not.
     *
     * @return boolean
     */
    public function isInternal();

    /**
     * Sets whether the attachment is internal or not.
     *
     * @param boolean $internal
     *
     * @return $this|AttachmentInterface
     */
    public function setInternal($internal);
}
