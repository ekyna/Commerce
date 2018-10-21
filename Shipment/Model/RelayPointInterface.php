<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;

/**
 * Interface RelayPointInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface RelayPointInterface extends ResourceInterface, AddressInterface, TimestampableInterface
{
    /**
     * Returns the number.
     *
     * @return string
     */
    public function getNumber();

    /**
     * Sets the number.
     *
     * @param string $number
     *
     * @return $this|RelayPointInterface
     */
    public function setNumber($number);

    /**
     * Returns the platform name.
     *
     * @return string
     */
    public function getPlatformName();

    /**
     * Sets the platform name.
     *
     * @param string $name
     *
     * @return $this|RelayPointInterface
     */
    public function setPlatformName($name);

    /**
     * Returns the platform data.
     *
     * @return array
     */
    public function getPlatformData();

    /**
     * Sets the platform data.
     *
     * @param array $data
     */
    public function setPlatformData(array $data);

    /**
     * Returns the opening hours.
     *
     * @return OpeningHour[]
     */
    public function getOpeningHours();

    /**
     * Adds the opening hour.
     *
     * @param OpeningHour $openingHour
     *
     * @return $this|RelayPointInterface
     */
    public function addOpeningHour(OpeningHour $openingHour);

    /**
     * Returns the distance (meters).
     *
     * @return int
     */
    public function getDistance();

    /**
     * Sets the distance.
     *
     * @param int $distance
     *
     * @return $this|RelayPointInterface
     */
    public function setDistance($distance);
}
