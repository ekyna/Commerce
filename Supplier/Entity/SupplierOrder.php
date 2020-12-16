<?php

namespace Ekyna\Component\Commerce\Supplier\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Stock\Model\WarehouseInterface;
use Ekyna\Component\Commerce\Supplier\Model;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderAttachmentInterface;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class SupplierOrder
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrder implements Model\SupplierOrderInterface
{
    use Common\NumberSubjectTrait,
        Common\ExchangeSubjectTrait,
        Common\StateSubjectTrait,
        TimestampableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\SupplierInterface
     */
    protected $supplier;

    /**
     * @var Model\SupplierCarrierInterface
     */
    protected $carrier;

    /**
     * @var WarehouseInterface
     */
    protected $warehouse;

    /**
     * @var ArrayCollection|Model\SupplierOrderItemInterface[]
     */
    protected $items;

    /**
     * @var ArrayCollection|Model\SupplierDeliveryInterface[]
     */
    protected $deliveries;

    /**
     * @var ArrayCollection|Model\SupplierOrderAttachmentInterface[]
     */
    protected $attachments;

    /**
     * @var float
     */
    protected $shippingCost;

    /**
     * @var float
     */
    protected $discountTotal;

    /**
     * @var float
     */
    protected $taxTotal;

    /**
     * @var float
     */
    protected $paymentTotal;

    /**
     * @var \DateTime
     */
    protected $paymentDate;

    /**
     * @var \DateTime
     */
    protected $paymentDueDate;

    /**
     * @var float
     */
    protected $customsTax;

    /**
     * @var float
     */
    protected $customsVat;

    /**
     * @var float
     */
    protected $forwarderFee;

    /**
     * @var float
     */
    protected $forwarderTotal;

    /**
     * @var \DateTime
     */
    protected $forwarderDate;

    /**
     * @var \DateTime
     */
    protected $forwarderDueDate;

    /**
     * @var \DateTime
     */
    protected $estimatedDateOfArrival;

    /**
     * @var array
     */
    protected $trackingUrls;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var \DateTime
     */
    protected $orderedAt;

    /**
     * @var \DateTime
     */
    protected $completedAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->state = Model\SupplierOrderStates::STATE_NEW;

        $this->shippingCost = 0.;
        $this->discountTotal = 0.;
        $this->taxTotal = 0.;
        $this->paymentTotal = 0.;
        $this->customsTax = 0.;
        $this->customsVat = 0.;
        $this->forwarderFee = 0.;
        $this->forwarderTotal = 0.;

        $this->items = new ArrayCollection();
        $this->deliveries = new ArrayCollection();
        $this->attachments = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->number ?: 'New supplier order';
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getSupplier(): ?Model\SupplierInterface
    {
        return $this->supplier;
    }

    /**
     * @inheritdoc
     */
    public function setSupplier(Model\SupplierInterface $supplier): Model\SupplierOrderInterface
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCarrier(): ?Model\SupplierCarrierInterface
    {
        return $this->carrier;
    }

    /**
     * @inheritdoc
     */
    public function setCarrier(Model\SupplierCarrierInterface $carrier = null): Model\SupplierOrderInterface
    {
        $this->carrier = $carrier;

        return $this;
    }

    /**
     * Returns the warehouse.
     *
     * @return WarehouseInterface
     */
    public function getWarehouse(): ?WarehouseInterface
    {
        return $this->warehouse;
    }

    /**
     * Sets the warehouse.
     *
     * @param WarehouseInterface $warehouse
     *
     * @return SupplierOrder
     */
    public function setWarehouse(WarehouseInterface $warehouse): Model\SupplierOrderInterface
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasItems(): bool
    {
        return 0 < $this->items->count();
    }

    /**
     * @inheritdoc
     */
    public function hasItem(Model\SupplierOrderItemInterface $item): bool
    {
        return $this->items->contains($item);
    }

    /**
     * @inheritdoc
     */
    public function addItem(Model\SupplierOrderItemInterface $item, $index = null): Model\SupplierOrderInterface
    {
        if (!$this->hasItem($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeItem(Model\SupplierOrderItemInterface $item): Model\SupplierOrderInterface
    {
        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
            $item->setOrder(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function hasDeliveries(): bool
    {
        return 0 < $this->deliveries->count();
    }

    /**
     * @inheritdoc
     */
    public function hasDelivery(Model\SupplierDeliveryInterface $delivery): bool
    {
        return $this->deliveries->contains($delivery);
    }

    /**
     * @inheritdoc
     */
    public function addDelivery(Model\SupplierDeliveryInterface $delivery): Model\SupplierOrderInterface
    {
        if (!$this->hasDelivery($delivery)) {
            $this->deliveries->add($delivery);
            $delivery->setOrder($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeDelivery(Model\SupplierDeliveryInterface $delivery): Model\SupplierOrderInterface
    {
        if ($this->hasDelivery($delivery)) {
            $this->deliveries->removeElement($delivery);
            $delivery->setOrder(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveries(): Collection
    {
        return $this->deliveries;
    }

    /**
     * @inheritDoc
     */
    public function hasAttachments(string $type = null): bool
    {
        if (null !== $type) {
            foreach ($this->attachments as $attachment) {
                if ($type === $attachment->getType()) {
                    return true;
                }
            }

            return false;
        }

        return 0 < $this->attachments->count();
    }

    /**
     * @inheritDoc
     */
    public function hasAttachment(SupplierOrderAttachmentInterface $attachment): bool
    {
        return $this->attachments->contains($attachment);
    }

    /**
     * @inheritDoc
     */
    public function addAttachment(SupplierOrderAttachmentInterface $attachment): Model\SupplierOrderInterface
    {
        if (!$this->hasAttachment($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setSupplierOrder($this);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeAttachment(SupplierOrderAttachmentInterface $attachment): Model\SupplierOrderInterface
    {
        if ($this->hasAttachment($attachment)) {
            $this->attachments->removeElement($attachment);
            $attachment->setSupplierOrder(null);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSupplierAttachments(): Collection
    {
        return $this->attachments->matching(
            Criteria::create()->where(Criteria::expr()->eq('internal', false))
        );
    }

    /**
     * @inheritDoc
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    /**
     * @inheritdoc
     */
    public function getShippingCost(): float
    {
        return $this->shippingCost;
    }

    /**
     * @inheritdoc
     */
    public function setShippingCost(float $amount): Model\SupplierOrderInterface
    {
        $this->shippingCost = $amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDiscountTotal(): float
    {
        return $this->discountTotal;
    }

    /**
     * @inheritdoc
     */
    public function setDiscountTotal(float $amount): Model\SupplierOrderInterface
    {
        $this->discountTotal = $amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTaxTotal(): float
    {
        return $this->taxTotal;
    }

    /**
     * @inheritdoc
     */
    public function setTaxTotal(float $amount): Model\SupplierOrderInterface
    {
        $this->taxTotal = $amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentTotal(): float
    {
        return $this->paymentTotal;
    }

    /**
     * @inheritdoc
     */
    public function setPaymentTotal(float $amount): Model\SupplierOrderInterface
    {
        $this->paymentTotal = $amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentDate(): ?DateTime
    {
        return $this->paymentDate;
    }

    /**
     * @inheritdoc
     */
    public function setPaymentDate(DateTime $date = null): Model\SupplierOrderInterface
    {
        $this->paymentDate = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentDueDate(): ?DateTime
    {
        return $this->paymentDueDate;
    }

    /**
     * @inheritdoc
     */
    public function setPaymentDueDate(DateTime $date = null): Model\SupplierOrderInterface
    {
        $this->paymentDueDate = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomsTax(): float
    {
        return $this->customsTax;
    }

    /**
     * @inheritdoc
     */
    public function setCustomsTax(float $amount): Model\SupplierOrderInterface
    {
        $this->customsTax = $amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomsVat(): float
    {
        return $this->customsVat;
    }

    /**
     * @inheritdoc
     */
    public function setCustomsVat(float $amount): Model\SupplierOrderInterface
    {
        $this->customsVat = $amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getForwarderFee(): float
    {
        return $this->forwarderFee;
    }

    /**
     * @inheritdoc
     */
    public function setForwarderFee(float $amount): Model\SupplierOrderInterface
    {
        $this->forwarderFee = $amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getForwarderTotal(): float
    {
        return $this->forwarderTotal;
    }

    /**
     * @inheritdoc
     */
    public function setForwarderTotal(float $amount): Model\SupplierOrderInterface
    {
        $this->forwarderTotal = $amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getForwarderDate(): ?DateTime
    {
        return $this->forwarderDate;
    }

    /**
     * @inheritdoc
     */
    public function setForwarderDate(DateTime $date = null): Model\SupplierOrderInterface
    {
        $this->forwarderDate = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getForwarderDueDate(): ?DateTime
    {
        return $this->forwarderDueDate;
    }

    /**
     * @inheritdoc
     */
    public function setForwarderDueDate(DateTime $date = null): Model\SupplierOrderInterface
    {
        $this->forwarderDueDate = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEstimatedDateOfArrival(): ?DateTime
    {
        return $this->estimatedDateOfArrival;
    }

    /**
     * @inheritdoc
     */
    public function setEstimatedDateOfArrival(DateTime $date = null): Model\SupplierOrderInterface
    {
        $this->estimatedDateOfArrival = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTrackingUrls(): ?array
    {
        return $this->trackingUrls;
    }

    /**
     * @inheritdoc
     */
    public function setTrackingUrls(array $urls = null): Model\SupplierOrderInterface
    {
        $this->trackingUrls = $urls;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function setDescription(string $description): Model\SupplierOrderInterface
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOrderedAt(): ?DateTime
    {
        return $this->orderedAt;
    }

    /**
     * @inheritdoc
     */
    public function setOrderedAt(DateTime $orderedAt = null): Model\SupplierOrderInterface
    {
        $this->orderedAt = $orderedAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCompletedAt(): ?DateTime
    {
        return $this->completedAt;
    }

    /**
     * @inheritdoc
     */
    public function setCompletedAt(DateTime $completedAt = null): Model\SupplierOrderInterface
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBaseCurrency(): ?string
    {
        if ($this->currency) {
            return $this->currency->getCode();
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getLocale(): ?string
    {
        if ($this->supplier) {
            return $this->supplier->getLocale();
        }

        return null;
    }
}
