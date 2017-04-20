<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Model;

/**
 * Interface NotificationsInterface
 * @package Ekyna\Component\Commerce\Customer\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface NotificationsInterface
{
    /**
     * @return array<string>
     */
    public function getNotifications(): array;

    /**
     * @param array<string> $notifications
     *
     * @return $this|NotificationsInterface
     */
    public function setNotifications(array $notifications = []): NotificationsInterface;
}
