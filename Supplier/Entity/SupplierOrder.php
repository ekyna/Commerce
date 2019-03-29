<?php

namespace Ekyna\Component\Commerce\Supplier\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Ekyna\Component\Commerce\Common\Model as Common;
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
        Common\CurrencySubjectTrait,
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
     * @var ArrayCollection|Model\SupplierOrderItemInterface[]
     */
    protected $items;

    /**
     * @var ArrayCollection|Model\SupplierDeliveryInterface[]
     */
    protected $deliveries;

    /**
     * @var float
     */
    protected $shippingCost = 0;

    /**
     * @var float
     */
    protected $discountTotal = 0;

    /**
     * @var float
     */
    protected $taxTotal = 0;

    /**
     * @var float
     */
    protected $paymentTotal = 0;

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
    protected $customsTax = 0;

    /**
     * @var float
     */
    protected $customsVat = 0;

    /**
     * @var float
     */
    protected $forwarderFee = 0;

    /**
     * @var float
     */
    protected $forwarderTotal = 0;

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
     * @var ArrayCollection|Model\SupplierOrderAttachmentInterface[]
     */
    protected $attachments;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->state = Model\SupplierOrderStates::STATE_NEW;

        $this->items = new ArrayCollection();
        $this->deliveries = new ArrayCollection();
        $this->attachments = new ArrayCollection();
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
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @inheritdoc
     */
    public function setSupplier(Model\SupplierInterface $supplier)
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCarrier()
    {
        return $this->carrier;
    }

    /**
     * @inheritdoc
     */
    public function setCarrier(Model\SupplierCarrierInterface $carrier = null)
    {
        $this->carrier = $carrier;

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
    public function hasItem(Model\SupplierOrderItemInterface $item)
    {
        return $this->items->contains($item);
    }

    /**
     * @inheritdoc
     */
    public function addItem(Model\SupplierOrderItemInterface $item, $index = null)
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
    public function removeItem(Model\SupplierOrderItemInterface $item)
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
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function hasDeliveries()
    {
        return 0 < $this->deliveries->count();
    }

    /**
     * @inheritdoc
     */
    public function hasDelivery(Model\SupplierDeliveryInterface $delivery)
    {
        return $this->deliveries->contains($delivery);
    }

    /**
     * @inheritdoc
     */
    public function addDelivery(Model\SupplierDeliveryInterface $delivery)
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
    public function removeDelivery(Model\SupplierDeliveryInterface $delivery)
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
    public function getDeliveries()
    {
        return $this->deliveries;
    }

    /**
     * @inheritdoc
     */
    public function getShippingCost()
    {
        return $this->shippingCost;
    }

    /**
     * @inheritdoc
     */
    public function setShippingCost($amount)
    {
        $this->shippingCost = (float)$amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDiscountTotal()
    {
        return $this->discountTotal;
    }

    /**
     * @inheritdoc
     */
    public function setDiscountTotal($amount)
    {
        $this->discountTotal = (float)$amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTaxTotal()
    {
        return $this->taxTotal;
    }

    /**
     * @inheritdoc
     */
    public function setTaxTotal($amount)
    {
        $this->taxTotal = (float)$amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentTotal()
    {
        return $this->paymentTotal;
    }

    /**
     * @inheritdoc
     */
    public function setPaymentTotal($amount)
    {
        $this->paymentTotal = (float)$amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * @inheritdoc
     */
    public function setPaymentDate(\DateTime $date = null)
    {
        $this->paymentDate = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentDueDate()
    {
        return $this->paymentDueDate;
    }

    /**
     * @inheritdoc
     */
    public function setPaymentDueDate(\DateTime $date = null)
    {
        $this->paymentDueDate = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomsTax()
    {
        return $this->customsTax;
    }

    /**
     * @inheritdoc
     */
    public function setCustomsTax($amount)
    {
        $this->customsTax = (float)$amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomsVat()
    {
        return $this->customsVat;
    }

    /**
     * @inheritdoc
     */
    public function setCustomsVat($amount)
    {
        $this->customsVat = (float)$amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getForwarderFee()
    {
        return $this->forwarderFee;
    }

    /**
     * @inheritdoc
     */
    public function setForwarderFee($amount)
    {
        $this->forwarderFee = (float)$amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getForwarderTotal()
    {
        return $this->forwarderTotal;
    }

    /**
     * @inheritdoc
     */
    public function setForwarderTotal($amount)
    {
        $this->forwarderTotal = (float)$amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getForwarderDate()
    {
        return $this->forwarderDate;
    }

    /**
     * @inheritdoc
     */
    public function setForwarderDate(\DateTime $date = null)
    {
        $this->forwarderDate = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getForwarderDueDate()
    {
        return $this->forwarderDueDate;
    }

    /**
     * @inheritdoc
     */
    public function setForwarderDueDate(\DateTime $date = null)
    {
        $this->forwarderDueDate = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEstimatedDateOfArrival()
    {
        return $this->estimatedDateOfArrival;
    }

    /**
     * @inheritdoc
     */
    public function setEstimatedDateOfArrival(\DateTime $date = null)
    {
        $this->estimatedDateOfArrival = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTrackingUrls()
    {
        return $this->trackingUrls;
    }

    /**
     * @inheritdoc
     */
    public function setTrackingUrls(array $urls = [])
    {
        $this->trackingUrls = $urls;

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
    }

    /**
     * @inheritdoc
     */
    public function getOrderedAt()
    {
        return $this->orderedAt;
    }

    /**
     * @inheritdoc
     */
    public function setOrderedAt(\DateTime $orderedAt = null)
    {
        $this->orderedAt = $orderedAt;

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
     * @inheritDoc
     */
    public function hasAttachments($type = null)
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
    public function hasAttachment(SupplierOrderAttachmentInterface $attachment)
    {
        return $this->attachments->contains($attachment);
    }

    /**
     * @inheritDoc
     */
    public function addAttachment(SupplierOrderAttachmentInterface $attachment)
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
    public function removeAttachment(SupplierOrderAttachmentInterface $attachment)
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
    public function getSupplierAttachments()
    {
        return $this->attachments->matching(
            Criteria::create()->where(Criteria::expr()->eq('internal', false))
        );
    }

    /**
     * @inheritDoc
     */
    public function getAttachments()
    {
        return $this->attachments;
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
