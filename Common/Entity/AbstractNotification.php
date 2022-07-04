<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use DateTimeInterface;
use Ekyna\Component\Commerce\Common\Model\NotificationInterface;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class NotificationLog
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractNotification extends AbstractResource implements NotificationInterface
{
    protected ?string            $type    = null;
    protected array              $data    = [];
    protected ?DateTimeInterface $sentAt  = null;
    protected ?string            $details = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): NotificationInterface
    {
        $this->type = $type;

        return $this;
    }

    public function hasData(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function getData(string $key): ?string
    {
        if (!$this->hasData($key)) {
            return null;
        }

        return $this->data[$key];
    }

    public function setData(string $key, string $data): NotificationInterface
    {
        $this->data[$key] = $data;

        return $this;
    }

    public function getSentAt(): ?DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(DateTimeInterface $date): NotificationInterface
    {
        $this->sentAt = $date;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(string $details): NotificationInterface
    {
        $this->details = $details;

        return $this;
    }
}
