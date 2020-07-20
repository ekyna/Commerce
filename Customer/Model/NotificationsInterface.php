<?php

namespace Ekyna\Component\Commerce\Customer\Model;

/**
 * Interface NotificationsInterface
 * @package Ekyna\Component\Commerce\Customer\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface NotificationsInterface
{
    /**
     * Returns the notifications.
     *
     * @return string[]
     */
    public function getNotifications(): array;

    /**
     * Sets the notifications.
     *
     * @param string[] $notifications
     *
     * @return $this|NotificationsInterface
     */
    public function setNotifications(array $notifications = []): NotificationsInterface;
}
