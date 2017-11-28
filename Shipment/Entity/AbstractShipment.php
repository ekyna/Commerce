<?php

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class AbstractShipment
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractShipment implements Shipment\ShipmentInterface
{
    use Common\NumberSubjectTrait,
        Common\StateSubjectTrait,
        TimestampableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Shipment\ShipmentMethodInterface
     */
    protected $method;

    /**
     * @var bool
     */
    protected $autoInvoice;

    /**
     * @var ArrayCollection|Shipment\ShipmentItemInterface[]
     */
    protected $items;

    /**
     * @var bool
     */
    protected $return;

    /**
     * @var float
     */
    protected $weight;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $trackingNumber;

    /**
     * @var array
     */
    protected $gatewayData;

    /**
     * @var \DateTime
     */
    protected $completedAt;

    /**
     * @var array
     */
    protected $senderAddress;

    /**
     * @var array
     */
    protected $receiverAddress;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->state = Shipment\ShipmentStates::STATE_NEW;
        $this->items = new ArrayCollection();
        $this->return = false;
        $this->autoInvoice = true;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->getNumber();
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
    public function isAutoInvoice()
    {
        return $this->autoInvoice;
    }

    /**
     * @inheritdoc
     */
    public function setAutoInvoice($auto)
    {
        $this->autoInvoice = (bool)$auto;

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
            $item->setShipment(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setItems(ArrayCollection $items)
    {
        $this->items = new ArrayCollection();

        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isReturn()
    {
        return $this->return;
    }

    /**
     * @inheritdoc
     */
    public function setReturn($return)
    {
        $this->return = (bool)$return;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @inheritdoc
     */
    public function setWeight($weight)
    {
        $this->weight = 0 < $weight ? (float)$weight : null;

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
    public function getTrackingNumber()
    {
        return $this->trackingNumber;
    }

    /**
     * @inheritdoc
     */
    public function setTrackingNumber($number)
    {
        $this->trackingNumber = $number;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPlatformName()
    {
        if ($this->method) {
            return $this->method->getPlatformName();
        }

        throw new LogicException("Shipment method is not set.");
    }

    /**
     * @inheritdoc
     */
    public function getGatewayName()
    {
        if ($this->method) {
            return $this->method->getGatewayName();
        }

        throw new LogicException("Shipment method is not set.");
    }

    /**
     * @inheritdoc
     */
    public function getGatewayData()
    {
        return $this->gatewayData;
    }

    /**
     * @inheritdoc
     */
    public function setGatewayData(array $data = null)
    {
        $this->gatewayData = $data;

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

    /**
     * @inheritdoc
     */
    public function getSenderAddress()
    {
        return $this->senderAddress;
    }

    /**
     * @inheritdoc
     */
    public function setSenderAddress($data)
    {
        $this->senderAddress = empty($data) ? null : $data;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReceiverAddress()
    {
        return $this->receiverAddress;
    }

    /**
     * @inheritdoc
     */
    public function setReceiverAddress($data)
    {
        $this->receiverAddress = empty($data) ? null : $data;

        return $this;
    }
}
