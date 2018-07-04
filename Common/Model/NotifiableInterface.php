<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\Collection;

/**
 * Interface NotifiableInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface NotifiableInterface
{
    /**
     * Returns whether auto notify is enabled.
     *
     * @return bool
     */
    public function isAutoNotify();

    /**
     * Sets whether auto notify is enabled.
     *
     * @param bool $enabled
     *
     * @return $this|NotificationInterface
     */
    public function setAutoNotify($enabled);

    /**
     * Returns whether the notifiable has notifications or not, optionally filtered by type.
     *
     * @param string $type
     *
     * @return bool
     */
    public function hasNotifications($type = null);

    /**
     * Returns whether the notifiable has the notification or not.
     *
     * @param NotificationInterface $notification
     *
     * @return bool
     */
    public function hasNotification(NotificationInterface $notification);

    /**
     * Adds the notification.
     *
     * @param NotificationInterface $notification
     *
     * @return $this|NotifiableInterface
     */
    public function addNotification(NotificationInterface $notification);

    /**
     * Removes the notification.
     *
     * @param NotificationInterface $notification
     *
     * @return $this|NotifiableInterface
     */
    public function removeNotification(NotificationInterface $notification);

    /**
     * Returns the notifications, optionally filtered by type.
     *
     * @param string $type
     *
     * @return Collection|NotificationInterface[]
     */
    public function getNotifications($type = null);
}
