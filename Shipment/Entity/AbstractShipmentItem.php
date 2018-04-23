<?php

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Shipment\Model;

/**
 * Class ShipmentItem
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractShipmentItem implements Model\ShipmentItemInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\ShipmentInterface
     */
    protected $shipment;

    /**
     * @var float
     */
    protected $quantity = 0;

    /**
     * @var ArrayCollection
     */
    protected $children;

    /**
     * @var float
     */
    protected $expected;

    /**
     * @var float
     */
    protected $available;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->clearChildren();
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
    public function getShipment()
    {
        return $this->shipment;
    }

    /**
     * @inheritdoc
     */
    public function setShipment(Model\ShipmentInterface $shipment = null)
    {
        if ($this->shipment !== $shipment) {
            if ($previous = $this->shipment) {
                $this->shipment = null;
                $previous->removeItem($this);
            }

            if ($this->shipment = $shipment) {
                $this->shipment->addItem($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @inheritdoc
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setChildren(array $children)
    {
        $this->clearChildren();

        foreach ($children as $child) {
            $this->children->add($child);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @inheritdoc
     */
    public function clearChildren()
    {
        $this->children = new ArrayCollection();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExpected()
    {
        return $this->expected;
    }

    /**
     * @inheritdoc
     */
    public function setExpected($expected)
    {
        $this->expected = (float)$expected;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAvailable()
    {
        return $this->available;
    }

    /**
     * @inheritdoc
     */
    public function setAvailable($available)
    {
        $this->available = (float)$available;

        return $this;
    }
}
