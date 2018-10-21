<?php

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAddress;
use Ekyna\Component\Commerce\Shipment\Model\OpeningHour;
use Ekyna\Component\Commerce\Shipment\Model\RelayPointInterface;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class RelayPoint
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RelayPoint extends AbstractAddress implements RelayPointInterface
{
    use TimestampableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $platformName;

    /**
     * @var array
     */
    protected $platformData = [];

    /**
     * @var string
     */
    protected $number;

    /**
     * @var OpeningHour[]
     */
    protected $openingHours = [];

    // TODO protected $holidays;

    /**
     * (not persisted)
     *
     * @var int
     */
    protected $distance;


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getPlatformName()
    {
        return $this->platformName;
    }

    /**
     * @inheritdoc
     */
    public function setPlatformName($name)
    {
        $this->platformName = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPlatformData()
    {
        return $this->platformData;
    }

    /**
     * @inheritdoc
     */
    public function setPlatformData(array $platformData)
    {
        $this->platformData = $platformData;
    }

    /**
     * @inheritdoc
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @inheritdoc
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOpeningHours()
    {
        return $this->openingHours;
    }

    /**
     * @inheritdoc
     */
    public function addOpeningHour(OpeningHour $openingHour)
    {
        $this->openingHours[] = $openingHour;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @inheritdoc
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }
}
