<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model\NotificationInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class NotificationLog
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractNotification implements NotificationInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var \DateTime
     */
    protected $sentAt;

    /**
     * @var string
     */
    protected $details;


    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns whether the notification has data, optionally for the given key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasData($key = null)
    {
        if (!is_null($key)) {
            return isset($this->data[$key]);
        }

        return !empty($this->data);
    }

    /**
     * @inheritDoc
     */
    public function getData($key = null)
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
    public function setData($data, $key = null)
    {
        if (!is_null($key)) {
            if (!is_scalar($data)) {
                throw new InvalidArgumentException("Expected scalar data.");
            }

            $this->data[$key] = $data;
        } elseif (!is_array($data)) {
            throw new InvalidArgumentException("Expected array data.");
        } else {
            $this->data = $data;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * @inheritDoc
     */
    public function setSentAt(\DateTime $date = null)
    {
        $this->sentAt = $date;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @inheritDoc
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }
}
