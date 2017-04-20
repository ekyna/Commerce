<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Trait NotifiableTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait NotifiableTrait
{
    protected bool $autoNotify;
    /** @var Collection|NotificationInterface[] */
    protected $notifications;


    protected function initializeNotifications(): void
    {
        $this->autoNotify = true;
        $this->notifications = new ArrayCollection();
    }

    public function isAutoNotify(): bool
    {
        return $this->autoNotify;
    }

    /**
     * @return $this|NotifiableInterface
     */
    public function setAutoNotify(bool $enabled): NotifiableInterface
    {
        $this->autoNotify = $enabled;

        return $this;
    }

    public function hasNotifications(string $type = null): bool
    {
        if ($type) {
            NotificationTypes::isValid($type);

            return 0 < $this->getNotifications($type)->count();
        }

        return 0 < $this->notifications->count();
    }

    public function getNotifications(string $type = null): Collection
    {
        if (null !== $type) {
            NotificationTypes::isValid($type);

            return $this
                ->notifications
                ->filter(function (NotificationInterface $n) use ($type) {
                    return $n->getType() === $type;
                });
        }

        return $this->notifications;
    }
}
