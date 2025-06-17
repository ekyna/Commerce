<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Entity;

use DateTime;
use DateTimeInterface;
use Ekyna\Component\Commerce\Common\Entity\AbstractSale;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\Model\NotifiableInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Quote\Model;

/**
 * Class Quote
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Quote extends AbstractSale implements Model\QuoteInterface
{
    use Common\InitiatorSubjectTrait;
    use Common\FollowerSubjectTrait;

    protected bool                     $editable     = false;
    protected ?Common\ProjectInterface $project      = null;
    protected ?DateTimeInterface       $expiresAt    = null;
    protected ?DateTimeInterface       $projectDate  = null;
    protected ?int                     $projectTrust = null;
    protected ?bool                    $projectAlive = null;


    public function __construct()
    {
        parent::__construct();

        $this->state = Model\QuoteStates::STATE_NEW;
        $this->source = Common\SaleSources::SOURCE_COMMERCIAL;
    }

    /**
     * @return Model\QuoteAddressInterface|null
     */
    public function getInvoiceAddress(): ?Common\SaleAddressInterface
    {
        return $this->invoiceAddress;
    }

    public function setInvoiceAddress(?Common\SaleAddressInterface $address): Common\SaleInterface
    {
        if ($address && !$address instanceof Model\QuoteAddressInterface) {
            throw new UnexpectedTypeException($address, Model\QuoteAddressInterface::class);
        }

        if ($address === $this->invoiceAddress) {
            return $this;
        }

        if ($previous = $this->invoiceAddress) {
            $this->invoiceAddress = null;
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $previous->setInvoiceQuote(null);
        }

        if ($this->invoiceAddress = $address) {
            $address->setInvoiceQuote($this);
        }

        return $this;
    }

    /**
     * @return Model\QuoteAddressInterface|null
     */
    public function getDeliveryAddress(): ?Common\SaleAddressInterface
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(Common\SaleAddressInterface $address = null): Common\SaleInterface
    {
        if ($address && !$address instanceof Model\QuoteAddressInterface) {
            throw new UnexpectedTypeException($address, Model\QuoteAddressInterface::class);
        }

        if ($address === $this->deliveryAddress) {
            return $this;
        }

        if ($previous = $this->deliveryAddress) {
            $this->deliveryAddress = null;
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $previous->setDeliveryQuote(null);
        }

        if ($this->deliveryAddress = $address) {
            $address->setDeliveryQuote($this);
        }

        return $this;
    }

    /**
     * @return Model\QuoteAddressInterface|null
     */
    public function getDestinationAddress(): ?Common\SaleAddressInterface
    {
        return $this->destinationAddress;
    }

    public function setDestinationAddress(Common\SaleAddressInterface $address = null): Common\SaleInterface
    {
        if ($address && !$address instanceof Model\QuoteAddressInterface) {
            throw new UnexpectedTypeException($address, Model\QuoteAddressInterface::class);
        }

        if ($address === $this->destinationAddress) {
            return $this;
        }

        if ($previous = $this->destinationAddress) {
            $this->destinationAddress = null;
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $previous->setDestinationQuote(null);
        }

        if ($this->destinationAddress = $address) {
            $address->setDestinationQuote($this);
        }

        return $this;
    }

    public function hasAttachment(Common\SaleAttachmentInterface $attachment): bool
    {
        if (!$attachment instanceof Model\QuoteAttachmentInterface) {
            throw new UnexpectedTypeException($attachment, Model\QuoteAttachmentInterface::class);
        }

        return $this->attachments->contains($attachment);
    }

    public function addAttachment(Common\SaleAttachmentInterface $attachment): Common\SaleInterface
    {
        if (!$attachment instanceof Model\QuoteAttachmentInterface) {
            throw new UnexpectedTypeException($attachment, Model\QuoteAttachmentInterface::class);
        }

        if (!$this->hasAttachment($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setQuote($this);
        }

        return $this;
    }

    public function removeAttachment(Common\SaleAttachmentInterface $attachment): Common\SaleInterface
    {
        if (!$attachment instanceof Model\QuoteAttachmentInterface) {
            throw new UnexpectedTypeException($attachment, Model\QuoteAttachmentInterface::class);
        }

        if ($this->hasAttachment($attachment)) {
            $this->attachments->removeElement($attachment);
            $attachment->setQuote(null);
        }

        return $this;
    }

    public function hasItem(Common\SaleItemInterface $item): bool
    {
        if (!$item instanceof Model\QuoteItemInterface) {
            throw new UnexpectedTypeException($item, Model\QuoteItemInterface::class);
        }

        return $this->items->contains($item);
    }

    public function addItem(Common\SaleItemInterface $item): Common\SaleInterface
    {
        if (!$item instanceof Model\QuoteItemInterface) {
            throw new UnexpectedTypeException($item, Model\QuoteItemInterface::class);
        }

        if (!$this->hasItem($item)) {
            $this->items->add($item);
            $item->setQuote($this);
        }

        return $this;
    }

    public function removeItem(Common\SaleItemInterface $item): Common\SaleInterface
    {
        if (!$item instanceof Model\QuoteItemInterface) {
            throw new UnexpectedTypeException($item, Model\QuoteItemInterface::class);
        }

        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
            $item->setQuote(null);
        }

        return $this;
    }

    public function hasAdjustment(Common\AdjustmentInterface $adjustment): bool
    {
        if (!$adjustment instanceof Model\QuoteAdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, Model\QuoteAdjustmentInterface::class);
        }

        return $this->adjustments->contains($adjustment);
    }

    public function addAdjustment(Common\AdjustmentInterface $adjustment): Common\AdjustableInterface
    {
        if (!$adjustment instanceof Model\QuoteAdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, Model\QuoteAdjustmentInterface::class);
        }

        if (!$this->hasAdjustment($adjustment)) {
            $this->adjustments->add($adjustment);
            $adjustment->setQuote($this);
        }

        return $this;
    }

    public function removeAdjustment(Common\AdjustmentInterface $adjustment): Common\AdjustableInterface
    {
        if (!$adjustment instanceof Model\QuoteAdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, Model\QuoteAdjustmentInterface::class);
        }

        if ($this->hasAdjustment($adjustment)) {
            $this->adjustments->removeElement($adjustment);
            $adjustment->setQuote(null);
        }

        return $this;
    }

    public function hasNotification(Common\NotificationInterface $notification): bool
    {
        if (!$notification instanceof Model\QuoteNotificationInterface) {
            throw new UnexpectedTypeException($notification, Model\QuoteNotificationInterface::class);
        }

        return $this->notifications->contains($notification);
    }

    public function addNotification(Common\NotificationInterface $notification): NotifiableInterface
    {
        if (!$notification instanceof Model\QuoteNotificationInterface) {
            throw new UnexpectedTypeException($notification, Model\QuoteNotificationInterface::class);
        }

        if (!$this->hasNotification($notification)) {
            $this->notifications->add($notification);
            $notification->setQuote($this);
        }

        return $this;
    }

    public function removeNotification(Common\NotificationInterface $notification): NotifiableInterface
    {
        if (!$notification instanceof Model\QuoteNotificationInterface) {
            throw new UnexpectedTypeException($notification, Model\QuoteNotificationInterface::class);
        }

        if ($this->hasNotification($notification)) {
            $this->notifications->removeElement($notification);
            $notification->setQuote(null);
        }

        return $this;
    }

    public function hasPayment(Payment\PaymentInterface $payment): bool
    {
        if (!$payment instanceof Model\QuotePaymentInterface) {
            throw new UnexpectedTypeException($payment, Model\QuotePaymentInterface::class);
        }

        return $this->payments->contains($payment);
    }

    public function addPayment(Payment\PaymentInterface $payment): Payment\PaymentSubjectInterface
    {
        if (!$payment instanceof Model\QuotePaymentInterface) {
            throw new UnexpectedTypeException($payment, Model\QuotePaymentInterface::class);
        }

        if (!$this->hasPayment($payment)) {
            $this->payments->add($payment);
            $payment->setQuote($this);
        }

        return $this;
    }

    public function removePayment(Payment\PaymentInterface $payment): Payment\PaymentSubjectInterface
    {
        if (!$payment instanceof Model\QuotePaymentInterface) {
            throw new UnexpectedTypeException($payment, Model\QuotePaymentInterface::class);
        }

        if ($this->hasPayment($payment)) {
            $this->payments->removeElement($payment);
            $payment->setQuote(null);
        }

        return $this;
    }

    public function getProject(): ?Common\ProjectInterface
    {
        return $this->project;
    }

    public function setProject(?Common\ProjectInterface $project): Model\QuoteInterface
    {
        if ($this->project === $project) {
            return $this;
        }

        if ($previous = $this->project) {
            $this->project = null;
            $previous->removeQuote($this);
        }

        if ($this->project = $project) {
            $this->project->addQuote($this);
        }

        return $this;
    }

    public function isEditable(): bool
    {
        return $this->editable;
    }

    public function setEditable(bool $editable): Model\QuoteInterface
    {
        $this->editable = $editable;

        return $this;
    }

    public function getExpiresAt(): ?DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?DateTimeInterface $expiresAt): Model\QuoteInterface
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function isExpired(): bool
    {
        if (null === $this->expiresAt) {
            return false;
        }

        $diff = $this->expiresAt->diff((new DateTime())->setTime(0, 0));

        return 0 < $diff->days && !$diff->invert;
    }

    public function getProjectDate(): ?DateTimeInterface
    {
        return $this->projectDate;
    }

    public function setProjectDate(?DateTimeInterface $date): Model\QuoteInterface
    {
        $this->projectDate = $date;

        return $this;
    }

    public function getProjectTrust(): ?int
    {
        return $this->projectTrust;
    }

    public function setProjectTrust(?int $trust): Model\QuoteInterface
    {
        $this->projectTrust = $trust;

        return $this;
    }

    public function getProjectAlive(): ?bool
    {
        return $this->projectAlive;
    }

    public function setProjectAlive(?bool $projectAlive): Model\QuoteInterface
    {
        $this->projectAlive = $projectAlive;

        return $this;
    }

    public function requiresVoucher(): bool
    {
        if (!$this->customer) {
            return false;
        }

        return $this->customer->hasParent() || $this->customer->hasChildren();
    }

    public function hasVoucher(): bool
    {
        return !empty($this->voucherNumber) && null !== $this->getVoucherAttachment();
    }

    public function getVoucherAttachment(): ?Model\QuoteAttachmentInterface
    {
        foreach ($this->attachments as $attachment) {
            if ($attachment->getType() === DocumentTypes::TYPE_VOUCHER) {
                return $attachment;
            }
        }

        return null;
    }
}
