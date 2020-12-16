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
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var boolean
     */
    protected $internal = false;


    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getFilename() ?: 'New attachment';
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
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
