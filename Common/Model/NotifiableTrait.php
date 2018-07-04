<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Trait NotifiableTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait NotifiableTrait
{
    /**
     * @var bool
     */
    protected $autoNotify;

    /**
     * @var ArrayCollection|NotificationInterface[]
     */
    protected $notifications;


    /**
     * Initializes the notifications.
     */
    protected function initializeNotifications()
    {
        $this->autoNotify = true;
        $this->notifications = new ArrayCollection();
    }

    /**
     * Returns whether notify is enabled.
     *
     * @return bool
     */
    public function isAutoNotify()
    {
        return $this->autoNotify;
    }

    /**
     * Sets whether notify is enabled.
     *
     * @param bool $enabled
     *
     * @return $this|NotificationInterface
     */
    public function setAutoNotify($enabled)
    {
        $this->autoNotify = $enabled;

        return $this;
    }

    /**
     * Returns whether the notifiable has notifications or not, optionally filtered by type.
     *
     * @param string $type
     *
     * @return bool
     */
    public function hasNotifications($type = null)
    {
        if (null !== $type) {
            NotificationTypes::isValidType($type);

            return $this->getNotifications($type)->count();
        }

        return 0 < $this->notifications->count();
    }

    /**
     * Returns the notifications, optionally filtered by type.
     *
     * @param string $type
     *
     * @return ArrayCollection|NotificationInterface[]
     */
    public function getNotifications($type = null)
    {
        if (null !== $type) {
            NotificationTypes::isValidType($type);

            return $this
                ->notifications
                ->filter(function (NotificationInterface $n) use ($type) {
                    return $n->getType() === $type;
                });
        }

        return $this->notifications;
    }
}
