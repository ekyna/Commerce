<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use DateTimeInterface;
use Ekyna\Component\Commerce\Common\Model\NotificationInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class NotificationLog
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractNotification implements NotificationInterface
{
    protected ?int               $id      = null;
    protected ?string            $type    = null;
    protected array              $data    = [];
    protected ?DateTimeInterface $sentAt  = null;
    protected ?string            $details = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): NotificationInterface
    {
        $this->type = $type;

        return $this;
    }

    public function hasData(?string $key): bool
    {
        if (!is_null($key)) {
            return isset($this->data[$key]);
        }

        return !empty($this->data);
    }

    /**
     * @inheritDoc
     */
    public function getData(?string $key = null)
    {
        if (!is_null($key)) {
            if (isset($this->data[$key])) {
                return $this->data[$key];
            }

            return null;
        }

        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function setData($data, ?string $key): NotificationInterface
    {
        if (!is_null($key)) {
            if (!is_scalar($data)) {
                throw new InvalidArgumentException('Expected scalar data.');
            }

            $this->data[$key] = $data;
        } elseif (!is_array($data)) {
            throw new InvalidArgumentException('Expected array data.');
        } else {
            $this->data = $data;
        }

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
