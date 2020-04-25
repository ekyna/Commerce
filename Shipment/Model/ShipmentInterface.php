<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Resource\Model as Resource;

/**
 * Interface ShipmentInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentInterface extends
    Resource\ResourceInterface,
    Resource\TimestampableInterface,
    Resource\LocalizedInterface,
    Common\NumberSubjectInterface,
    Common\StateSubjectInterface,
    ShipmentDataInterface
{
    /**
     * Returns the sale.
     *
     * @return Common\SaleInterface|ShipmentSubjectInterface
     */
    public function getSale();

    /**
     * Returns the invoice.
     *
     * @return InvoiceInterface
     */
    public function getInvoice();

    /**
     * Sets the invoice.
     *
     * @param InvoiceInterface|null $invoice
     *
     * @return $this|ShipmentInterface
     */
    public function setInvoice(InvoiceInterface $invoice = null);

    /**
     * Returns the method.
     *
     * @return ShipmentMethodInterface
     */
    public function getMethod();

    /**
     * Sets the method.
     *
     * @param ShipmentMethodInterface $method
     *
     * @return $this|ShipmentInterface
     */
    public function setMethod(ShipmentMethodInterface $method);

    /**
     * Returns whether the shipment has at least one item or not.
     *
     * @return bool
     */
    public function hasItems();

    /**
     * Returns the items.
     *
     * @return ArrayCollection|ShipmentItemInterface[]
     */
    public function getItems();

    /**
     * Returns whether the shipment has the item or not.
     *
     * @param ShipmentItemInterface $item
     *
     * @return bool
     */
    public function hasItem(ShipmentItemInterface $item);

    /**
     * Adds the item.
     *
     * @param ShipmentItemInterface $item
     *
     * @return $this
     */
    public function addItem(ShipmentItemInterface $item);

    /**
     * Removes the item.
     *
     * @param ShipmentItemInterface $item
     *
     * @return $this|ShipmentInterface
     */
    public function removeItem(ShipmentItemInterface $item);

    /**
     * Sets the shipment items.
     *
     * @param ArrayCollection|ShipmentItemInterface[] $items
     *
     * @return $this|ShipmentInterface
     */
    //public function setItems(ArrayCollection $items);

    /**
     * Returns whether the shipment has at least one parcel or not.
     *
     * @return bool
     */
    public function hasParcels();

    /**
     * Returns the parcels.
     *
     * @return ArrayCollection|ShipmentParcelInterface[]
     */
    public function getParcels();

    /**
     * Returns whether the shipment has the parcel or not.
     *
     * @param ShipmentParcelInterface $parcel
     *
     * @return bool
     */
    public function hasParcel(ShipmentParcelInterface $parcel);

    /**
     * Adds the parcel.
     *
     * @param ShipmentParcelInterface $parcel
     *
     * @return $this
     */
    public function addParcel(ShipmentParcelInterface $parcel);

    /**
     * Removes the parcel.
     *
     * @param ShipmentParcelInterface $parcel
     *
     * @return $this|ShipmentInterface
     */
    public function removeParcel(ShipmentParcelInterface $parcel);

    /**
     * Sets the shipment parcels.
     *
     * @param ArrayCollection|ShipmentParcelInterface[] $parcels
     *
     * @return $this|ShipmentInterface
     */
    //public function setParcels(ArrayCollection $parcels);

    /**
     * Returns whether or not an equivalent invoice should be generated automatically.
     *
     * @return bool
     */
    public function isAutoInvoice();

    /**
     * Sets whether or not an equivalent invoice should be generated automatically.
     *
     * @param bool $auto
     *
     * @return $this|ShipmentInterface
     */
    public function setAutoInvoice($auto);

    /**
     * Returns whether or not the shipment is a return.
     *
     * @return bool
     */
    public function isReturn();

    /**
     * Sets whether or not the shipment is a return.
     *
     * @param bool $return
     *
     * @return $this|ShipmentInterface
     */
    public function setReturn($return);

    /**
     * Returns the description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Sets the description.
     *
     * @param string $description
     *
     * @return $this|ShipmentInterface
     */
    public function setDescription($description);

    /**
     * Returns the platform name.
     *
     * @return string
     */
    public function getPlatformName();

    /**
     * Returns the gateway name.
     *
     * @return string
     */
    public function getGatewayName();

    /**
     * Returns the gateway data.
     *
     * @return array
     */
    public function getGatewayData();

    /**
     * Sets the gateway data.
     *
     * @param array $data
     *
     * @return $this|ShipmentInterface
     */
    public function setGatewayData(array $data = null);

    /**
     * Returns the "shipped at" date time.
     *
     * @return \DateTime
     */
    public function getShippedAt();

    /**
     * Sets the "shipped at" date time.
     *
     * @param \DateTime $shippedAt
     */
    public function setShippedAt(\DateTime $shippedAt = null);

    /**
     * Returns the completedAt.
     *
     * @return \DateTime
     */
    public function getCompletedAt();

    /**
     * Sets the completedAt.
     *
     * @param \DateTime $completedAt
     *
     * @return $this|ShipmentInterface
     */
    public function setCompletedAt(\DateTime $completedAt = null);

    /**
     * Returns the sender address data.
     *
     * @return array
     */
    public function getSenderAddress();

    /**
     * Sets the sender address data.
     *
     * @param array $data
     *
     * @return $this|ShipmentInterface
     */
    public function setSenderAddress($data);

    /**
     * Returns the receiver address data.
     *
     * @return array
     */
    public function getReceiverAddress();

    /**
     * Sets the receiver address data.
     *
     * @param array $data
     *
     * @return $this|ShipmentInterface
     */
    public function setReceiverAddress($data);

    /**
     * Returns the relay point.
     *
     * @return RelayPointInterface
     */
    public function getRelayPoint();

    /**
     * Sets the relay point.
     *
     * @param RelayPointInterface $relayPoint
     *
     * @return $this|ShipmentInterface
     */
    public function setRelayPoint(RelayPointInterface $relayPoint = null);

    /**
     * Returns whether the shipment is empty
     * (do not have at least one item with quantity greater than zero).
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Returns whether this shipment is a partial one.
     *
     * @return bool
     */
    public function isPartial();
}
