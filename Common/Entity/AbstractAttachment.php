<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Bundle\CoreBundle\Model\UploadableTrait;
use Ekyna\Component\Commerce\Common\Model\AttachmentInterface;

/**
 * Class AbstractAttachment
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAttachment implements AttachmentInterface
{
    use UploadableTrait;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var boolean
     */
    protected $internal = false;


    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->guessFilename();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function isInternal()
    {
        return $this->internal;
    }

    /**
     * @inheritdoc
     */
    public function setInternal($internal)
    {
        $this->internal = (bool)$internal;

        return $this;
    }
}
