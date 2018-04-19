<?php

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\Commerce\Payment\Model as Payment;
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
        TimestampableTrait,
        Shipment\ShipmentDataTrait;

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
     * @var ArrayCollection|Shipment\ShipmentParcelInterface[]
     */
    protected $parcels;

    /**
     * @var bool
     */
    protected $autoInvoice;

    /**
     * @var bool
     */
    protected $return;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var array
     */
    protected $gatewayData;

    /**
     * @var \DateTime
     */
    protected $shippedAt;

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
     * @var Shipment\RelayPointInterface
     */
    protected $relayPoint;

    /**
     * @var Payment\PaymentMethodInterface
     */
    protected $creditMethod;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->state = Shipment\ShipmentStates::STATE_NEW;
        $this->items = new ArrayCollection();
        $this->parcels = new ArrayCollection();
        $this->return = false;
        $this->autoInvoice = true;

        $this->initializeShipmentData();
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
    public function hasParcels()
    {
        return 0 < $this->parcels->count();
    }

    /**
     * @inheritdoc
     */
    public function getParcels()
    {
        return $this->parcels;
    }

    /**
     * @inheritdoc
     */
    public function hasParcel(Shipment\ShipmentParcelInterface $parcel)
    {
        return $this->parcels->contains($parcel);
    }

    /**
     * @inheritdoc
     */
    public function addParcel(Shipment\ShipmentParcelInterface $parcel)
    {
        if (!$this->hasParcel($parcel)) {
            $this->parcels->add($parcel);
            $parcel->setShipment($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeParcel(Shipment\ShipmentParcelInterface $parcel)
    {
        if ($this->hasParcel($parcel)) {
            $this->parcels->removeElement($parcel);
            $parcel->setShipment(null);
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
     * Returns the "shipped at" date time.
     *
     * @return \DateTime
     */
    public function getShippedAt()
    {
        return $this->shippedAt;
    }

    /**
     * Sets the "shipped at" date time.
     *
     * @param \DateTime $shippedAt
     */
    public function setShippedAt(\DateTime $shippedAt = null)
    {
        $this->shippedAt = $shippedAt;
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

    /**
     * @inheritDoc
     */
    public function getRelayPoint()
    {
        return $this->relayPoint;
    }

    /**
     * @inheritDoc
     */
    public function setRelayPoint(Shipment\RelayPointInterface $relayPoint = null)
    {
        $this->relayPoint = $relayPoint;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreditMethod()
    {
        return $this->creditMethod;
    }

    /**
     * @inheritDoc
     */
    public function setCreditMethod(Payment\PaymentMethodInterface $method = null)
    {
        $this->creditMethod = $method;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isEmpty()
    {
        foreach ($this->items as $item) {
            if (0 < $item->getQuantity()) {
                return false;
            }
        }

        return true;
    }
}
