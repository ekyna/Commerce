<?php

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class AbstractShipment
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractShipment implements Shipment\ShipmentInterface
{
    use TimestampableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Shipment\ShipmentMethodInterface
     */
    protected $method;

    /**
     * @var ArrayCollection|Shipment\ShipmentItemInterface[]
     */
    protected $items;

    /**
     * @var string
     */
    protected $number;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var \DateTime
     */
    protected $completedAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->state = Shipment\ShipmentStates::STATE_NEW;
        $this->items = new ArrayCollection();
    }

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
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @inheritdoc
     */
    public function setMethod(Shipment\ShipmentMethodInterface $method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasItems()
    {
        return 0 < $this->items->count();
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function hasItem(Shipment\ShipmentItemInterface $item)
    {
        return $this->items->contains($item);
    }

    /**
     * @inheritdoc
     */
    public function addItem(Shipment\ShipmentItemInterface $item)
    {
        if (!$this->hasItem($item)) {
            $this->items->add($item);
            $item->setShipment($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeItem(Shipment\ShipmentItemInterface $item)
    {
        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
            //$item->setShipment(null);
        }

        return $this;
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
    public function getState()
    {
        return $this->state;
    }

    /**
     * @inheritdoc
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCompletedAt()
    {
        return $this->completedAt;
    }

    /**
     * @inheritdoc
     */
    public function setCompletedAt(\DateTime $completedAt = null)
    {
        $this->completedAt = $completedAt;

        return $this;
    }
}
