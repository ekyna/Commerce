<?php

namespace Ekyna\Component\Commerce\Common\Listener;

use Ekyna\Bundle\CoreBundle\Model\UploadableInterface;
use Ekyna\Bundle\CoreBundle\Uploader\UploaderInterface;

/**
 * Class UploadableListener
 * @package Ekyna\Component\Commerce\Common\Listener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UploadableListener
{
    /**
     * @var UploaderInterface
     */
    private $uploader;

    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * @param UploaderInterface $uploader
     */
    public function __construct(UploaderInterface $uploader)
    {
        $this->uploader = $uploader;
    }

    /**
     * Sets whether the listener is enabled.
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;
    }

    /**
     * Pre persist event handler.
     *
     * @param UploadableInterface $uploadable
     */
    public function prePersist(UploadableInterface $uploadable)
    {
        if (!$this->enabled) {
            return;
        }

        // TODO Remove (when handled by resource behavior).
        $uploadable
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        $this->uploader->prepare($uploadable);
    }

    /**
     * Post persist event handler.
     *
     * @param UploadableInterface $uploadable
     */
    public function postPersist(UploadableInterface $uploadable)
    {
        if (!$this->enabled) {
            return;
        }

        $this->uploader->upload($uploadable);
    }

    /**
     * Pre update event handler.
     *
     * @param UploadableInterface $uploadable
     */
    public function preUpdate(UploadableInterface $uploadable)
    {
        if (!$this->enabled) {
            return;
        }

        // TODO Remove (when handled by resource behavior).
        $uploadable->setUpdatedAt(new \DateTime());

        $this->uploader->prepare($uploadable);
    }

    /**
     * Post update event handler.
     *
     * @param UploadableInterface $uploadable
     */
    public function postUpdate(UploadableInterface $uploadable)
    {
        if (!$this->enabled) {
            return;
        }

        $this->uploader->upload($uploadable);
    }

    /**
     * Pre remove event handler.
     *
     * @param UploadableInterface $uploadable
     */
    public function preRemove(UploadableInterface $uploadable)
    {
        if (!$this->enabled) {
            return;
        }

        $uploadable->setOldPath($uploadable->getPath());
    }

    /**
     * Post remove event handler.
     *
     * @param UploadableInterface $uploadable
     */
    public function postRemove(UploadableInterface $uploadable)
    {
        if (!$this->enabled) {
            return;
        }

        $this->uploader->remove($uploadable);
    }
}
