<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\Collection;

/**
 * Interface NotifiableInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface NotifiableInterface
{
    public function isAutoNotify(): bool;

    public function setAutoNotify(bool $enabled): NotifiableInterface;

    /**
     * Returns whether the notifiable has notifications or not, optionally filtered by type.
     */
    public function hasNotifications(string $type = null): bool;

    public function hasNotification(NotificationInterface $notification): bool;

    public function addNotification(NotificationInterface $notification): NotifiableInterface;

    public function removeNotification(NotificationInterface $notification): NotifiableInterface;

    /**
     * Returns the notifications, optionally filtered by type.
     *
     * @return Collection|NotificationInterface[]
     */
    public function getNotifications(string $type = null): Collection;
}
