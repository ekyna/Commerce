<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface NotificationInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface NotificationInterface
{
    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType();

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return $this|NotificationInterface
     */
    public function setType($type);

    /**
     * Returns whether the notification has data, optionally for the given key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasData($key = null);

    /**
     * Returns the data, optionally for the given key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getData($key = null);

    /**
     * Sets the data, optionally for the given key.
     *
     * @param mixed $data
     * @param string $key
     *
     * @return $this|NotificationInterface
     */
    public function setData($data, $key = null);

    /**
     * Returns the sentAt.
     *
     * @return \DateTime
     */
    public function getSentAt();

    /**
     * Sets the sentAt.
     *
     * @param \DateTime $date
     *
     * @return $this|NotificationInterface
     */
    public function setSentAt(\DateTime $date = null);

    /**
     * Returns the details.
     *
     * @return string
     */
    public function getDetails();

    /**
     * Sets the details.
     *
     * @param string $details
     *
     * @return $this|NotificationInterface
     */
    public function setDetails($details);
}
