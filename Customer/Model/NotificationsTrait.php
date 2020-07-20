<?php

namespace Ekyna\Component\Commerce\Customer\Model;

use Ekyna\Component\Commerce\Common\Model\NotificationTypes;

/**
 * Trait NotificationsTrait
 * @package Ekyna\Component\Commerce\Customer\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait NotificationsTrait
{
    /**
     * @var string[]
     */
    protected $notifications;


    /**
     * Returns the notifications.
     *
     * @return string[]
     */
    public function getNotifications(): array
    {
        return $this->notifications;
    }

    /**
     * Sets the notifications.
     *
     * @param string[] $notifications
     *
     * @return $this|NotificationsInterface
     */
    public function setNotifications(array $notifications = []): NotificationsInterface
    {
        $this->notifications = [];

        foreach (array_unique($notifications) as $notification) {
            if (!NotificationTypes::isValid($notification, false)) {
                continue;
            }

            $this->notifications[] = $notification;
        }

        return $this;
    }
}
