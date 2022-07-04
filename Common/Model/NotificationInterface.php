<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use DateTimeInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface NotificationInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface NotificationInterface extends ResourceInterface
{
    public function getType(): ?string;

    public function setType(string $type): NotificationInterface;

    /**
     * Returns whether the notification has data for the given key.
     */
    public function hasData(string $key): bool;

    /**
     * Returns the data for the given key.
     */
    public function getData(string $key): ?string;

    /**
     * Sets the data for the given key.
     */
    public function setData(string $key, string $data): NotificationInterface;

    public function getSentAt(): ?DateTimeInterface;

    public function setSentAt(DateTimeInterface $date): NotificationInterface;

    public function getDetails(): ?string;

    public function setDetails(string $details): NotificationInterface;
}
