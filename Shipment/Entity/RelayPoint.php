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
     * @inheritDoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getPlatformName()
    {
        return $this->platformName;
    }

    /**
     * @inheritDoc
     */
    public function setPlatformName($name)
    {
        $this->platformName = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPlatformData()
    {
        return $this->platformData;
    }

    /**
     * @inheritDoc
     */
    public function setPlatformData(array $platformData)
    {
        $this->platformData = $platformData;
    }

    /**
     * @inheritDoc
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @inheritDoc
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOpeningHours()
    {
        return $this->openingHours;
    }

    /**
     * @inheritDoc
     */
    public function addOpeningHour(OpeningHour $openingHour)
    {
        $this->openingHours[] = $openingHour;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @inheritDoc
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }
}
