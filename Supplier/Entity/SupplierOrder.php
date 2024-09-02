<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Entity;

use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Stock\Model\WarehouseInterface;
use Ekyna\Component\Commerce\Supplier\Model;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderAttachmentInterface;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class SupplierOrder
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrder extends AbstractResource implements Model\SupplierOrderInterface
{
    use Common\ExchangeSubjectTrait;

    // TODO (Remove ExchangeSubjectTrait) use Common\CurrencySubjectTrait;
    use Common\NumberSubjectTrait;
    use Common\StateSubjectTrait;
    use TimestampableTrait;

    protected ?Model\SupplierInterface        $supplier  = null;
    protected ?Model\SupplierCarrierInterface $carrier   = null;
    protected ?WarehouseInterface             $warehouse = null;

    /** @var Collection<int, Model\SupplierOrderItemInterface> */
    protected Collection $items;
    /** @var Collection<int, Model\SupplierDeliveryInterface> */
    protected Collection $deliveries;
    /** @var Collection<int, Model\SupplierOrderAttachmentInterface> */
    protected Collection $attachments;
    /** @var Collection<int, Model\SupplierPaymentInterface> */
    protected Collection $payments;

    protected Decimal            $shippingCost;
    protected Decimal            $discountTotal;
    protected Decimal            $taxTotal;
    protected Decimal            $paymentTotal;
    protected Decimal            $paymentPaidTotal;
    protected ?DateTimeInterface $paymentDate    = null; // TODO Remove
    protected ?DateTimeInterface $paymentDueDate = null;

    protected bool               $reverseCharge    = false;
    protected Decimal            $customsTax;
    protected Decimal            $customsVat;
    protected Decimal            $forwarderFee;
    protected Decimal            $forwarderTotal;
    protected Decimal            $forwarderPaidTotal;
    protected ?DateTimeInterface $forwarderDate    = null; // TODO Remove
    protected ?DateTimeInterface $forwarderDueDate = null;

    protected ?DateTimeInterface $estimatedDateOfArrival = null;
    protected ?array             $trackingUrls           = null;
    protected ?string            $description            = null;

    protected ?DateTimeInterface $orderedAt   = null;
    protected ?DateTimeInterface $completedAt = null;


    public function __construct()
    {
        $this->state = Model\SupplierOrderStates::STATE_NEW;

        $this->shippingCost = new Decimal(0);
        $this->discountTotal = new Decimal(0);
        $this->taxTotal = new Decimal(0);
        $this->paymentTotal = new Decimal(0);
        $this->paymentPaidTotal = new Decimal(0);
        $this->customsTax = new Decimal(0);
        $this->customsVat = new Decimal(0);
        $this->forwarderFee = new Decimal(0);
        $this->forwarderTotal = new Decimal(0);
        $this->forwarderPaidTotal = new Decimal(0);

        $this->items = new ArrayCollection();
        $this->deliveries = new ArrayCollection();
        $this->attachments = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->number ?: 'New supplier order';
    }

    public function getSupplier(): ?Model\SupplierInterface
    {
        return $this->supplier;
    }

    public function setSupplier(?Model\SupplierInterface $supplier): Model\SupplierOrderInterface
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getCarrier(): ?Model\SupplierCarrierInterface
    {
        return $this->carrier;
    }

    public function setCarrier(?Model\SupplierCarrierInterface $carrier): Model\SupplierOrderInterface
    {
        $this->carrier = $carrier;

        return $this;
    }

    public function getWarehouse(): ?WarehouseInterface
    {
        return $this->warehouse;
    }

    public function setWarehouse(?WarehouseInterface $warehouse): Model\SupplierOrderInterface
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    public function hasItems(): bool
    {
        return 0 < $this->items->count();
    }

    public function hasItem(Model\SupplierOrderItemInterface $item): bool
    {
        return $this->items->contains($item);
    }

    public function addItem(Model\SupplierOrderItemInterface $item): Model\SupplierOrderInterface
    {
        if (!$this->hasItem($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }

        return $this;
    }

    public function removeItem(Model\SupplierOrderItemInterface $item): Model\SupplierOrderInterface
    {
        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
            $item->setOrder(null);
        }

        return $this;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function hasDeliveries(): bool
    {
        return 0 < $this->deliveries->count();
    }

    public function hasDelivery(Model\SupplierDeliveryInterface $delivery): bool
    {
        return $this->deliveries->contains($delivery);
    }

    public function addDelivery(Model\SupplierDeliveryInterface $delivery): Model\SupplierOrderInterface
    {
        if (!$this->hasDelivery($delivery)) {
            $this->deliveries->add($delivery);
            $delivery->setOrder($this);
        }

        return $this;
    }

    public function removeDelivery(Model\SupplierDeliveryInterface $delivery): Model\SupplierOrderInterface
    {
        if ($this->hasDelivery($delivery)) {
            $this->deliveries->removeElement($delivery);
            $delivery->setOrder(null);
        }

        return $this;
    }

    public function getDeliveries(): Collection
    {
        return $this->deliveries;
    }

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

    public function hasAttachment(SupplierOrderAttachmentInterface $attachment): bool
    {
        return $this->attachments->contains($attachment);
    }

    public function addAttachment(SupplierOrderAttachmentInterface $attachment): Model\SupplierOrderInterface
    {
        if (!$this->hasAttachment($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setSupplierOrder($this);
        }

        return $this;
    }

    public function removeAttachment(SupplierOrderAttachmentInterface $attachment): Model\SupplierOrderInterface
    {
        if ($this->hasAttachment($attachment)) {
            $this->attachments->removeElement($attachment);
            $attachment->setSupplierOrder(null);
        }

        return $this;
    }

    public function getSupplierAttachments(): Collection
    {
        return $this->attachments->matching(
            Criteria::create()->where(Criteria::expr()->eq('internal', false))
        );
    }

    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function hasPayments(bool $toForwarder = null): bool
    {
        if (null === $toForwarder) {
            return !$this->payments->isEmpty();
        }

        foreach ($this->payments as $payment) {
            if ($toForwarder === $payment->isToForwarder()) {
                return true;
            }
        }

        return false;
    }

    public function hasPayment(Model\SupplierPaymentInterface $payment): bool
    {
        return $this->payments->contains($payment);
    }

    public function addPayment(Model\SupplierPaymentInterface $payment): Model\SupplierOrderInterface
    {
        if (!$this->hasPayment($payment)) {
            $this->payments->add($payment);
            $payment->setOrder($this);
        }

        return $this;
    }

    public function removePayment(Model\SupplierPaymentInterface $payment): Model\SupplierOrderInterface
    {
        if ($this->hasPayment($payment)) {
            $this->payments->removeElement($payment);
            $payment->setOrder(null);
        }

        return $this;
    }

    public function getPayments(bool $toForwarder = null): Collection
    {
        if (null === $toForwarder) {
            return $this->payments;
        }

        return $this->payments->filter(
            static fn(Model\SupplierPaymentInterface $p): bool => $toForwarder === $p->isToForwarder()
        );
    }

    public function getShippingCost(): Decimal
    {
        return $this->shippingCost;
    }

    public function setShippingCost(Decimal $amount): Model\SupplierOrderInterface
    {
        $this->shippingCost = $amount;

        return $this;
    }

    public function getDiscountTotal(): Decimal
    {
        return $this->discountTotal;
    }

    public function setDiscountTotal(Decimal $amount): Model\SupplierOrderInterface
    {
        $this->discountTotal = $amount;

        return $this;
    }

    public function getTaxTotal(): Decimal
    {
        return $this->taxTotal;
    }

    public function setTaxTotal(Decimal $amount): Model\SupplierOrderInterface
    {
        $this->taxTotal = $amount;

        return $this;
    }

    public function getPaymentTotal(): Decimal
    {
        return $this->paymentTotal;
    }

    public function setPaymentTotal(Decimal $amount): Model\SupplierOrderInterface
    {
        $this->paymentTotal = $amount;

        return $this;
    }

    public function getPaymentPaidTotal(): Decimal
    {
        return $this->paymentPaidTotal;
    }

    public function setPaymentPaidTotal(Decimal $amount): Model\SupplierOrderInterface
    {
        $this->paymentPaidTotal = $amount;

        return $this;
    }

    public function getPaymentDate(): ?DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(?DateTimeInterface $date): Model\SupplierOrderInterface
    {
        $this->paymentDate = $date;

        return $this;
    }

    public function getPaymentDueDate(): ?DateTimeInterface
    {
        return $this->paymentDueDate;
    }

    public function setPaymentDueDate(?DateTimeInterface $date): Model\SupplierOrderInterface
    {
        $this->paymentDueDate = $date;

        return $this;
    }

    public function isReverseCharge(): bool
    {
        return $this->reverseCharge;
    }

    public function setReverseCharge(bool $reverseCharge): Model\SupplierOrderInterface
    {
        $this->reverseCharge = $reverseCharge;

        return $this;
    }

    public function getCustomsTax(): Decimal
    {
        return $this->customsTax;
    }

    public function setCustomsTax(Decimal $amount): Model\SupplierOrderInterface
    {
        $this->customsTax = $amount;

        return $this;
    }

    public function getCustomsVat(): Decimal
    {
        return $this->customsVat;
    }

    public function setCustomsVat(Decimal $amount): Model\SupplierOrderInterface
    {
        $this->customsVat = $amount;

        return $this;
    }

    public function getForwarderFee(): Decimal
    {
        return $this->forwarderFee;
    }

    public function setForwarderFee(Decimal $amount): Model\SupplierOrderInterface
    {
        $this->forwarderFee = $amount;

        return $this;
    }

    public function getForwarderTotal(): Decimal
    {
        return $this->forwarderTotal;
    }

    public function setForwarderTotal(Decimal $amount): Model\SupplierOrderInterface
    {
        $this->forwarderTotal = $amount;

        return $this;
    }

    public function getForwarderPaidTotal(): Decimal
    {
        return $this->forwarderPaidTotal;
    }

    public function setForwarderPaidTotal(Decimal $amount): Model\SupplierOrderInterface
    {
        $this->forwarderPaidTotal = $amount;

        return $this;
    }

    public function getForwarderDate(): ?DateTimeInterface
    {
        return $this->forwarderDate;
    }

    public function setForwarderDate(?DateTimeInterface $date): Model\SupplierOrderInterface
    {
        $this->forwarderDate = $date;

        return $this;
    }

    public function getForwarderDueDate(): ?DateTimeInterface
    {
        return $this->forwarderDueDate;
    }

    public function setForwarderDueDate(?DateTimeInterface $date): Model\SupplierOrderInterface
    {
        $this->forwarderDueDate = $date;

        return $this;
    }

    public function getEstimatedDateOfArrival(): ?DateTimeInterface
    {
        return $this->estimatedDateOfArrival;
    }

    public function setEstimatedDateOfArrival(?DateTimeInterface $date): Model\SupplierOrderInterface
    {
        $this->estimatedDateOfArrival = $date;

        return $this;
    }

    public function getTrackingUrls(): ?array
    {
        return $this->trackingUrls;
    }

    public function setTrackingUrls(?array $urls): Model\SupplierOrderInterface
    {
        $this->trackingUrls = $urls;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): Model\SupplierOrderInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getOrderedAt(): ?DateTimeInterface
    {
        return $this->orderedAt;
    }

    public function setOrderedAt(?DateTimeInterface $date): Model\SupplierOrderInterface
    {
        $this->orderedAt = $date;

        return $this;
    }

    public function getCompletedAt(): ?DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?DateTimeInterface $date): Model\SupplierOrderInterface
    {
        $this->completedAt = $date;

        return $this;
    }

    public function isPaid(): bool
    {
        return $this->paymentPaidTotal->equals($this->paymentTotal)
            && $this->forwarderPaidTotal->equals($this->forwarderTotal);
    }

    public function getBaseCurrency(): ?string
    {
        return $this->currency?->getCode();
    }

    public function getLocale(): ?string
    {
        return $this->supplier?->getLocale();
    }

    // TODO Remove next exchange rate & date methods

    public function getExchangeRate(): ?Decimal
    {
        return $this->exchangeRate ?? $this->resolveExchangeRate();
    }

    /**
     * @return $this|ExchangeSubjectInterface
     */
    public function setExchangeRate(?Decimal $rate): ExchangeSubjectInterface
    {
        throw new LogicException("Supplier order's exchange rate is resolved payments.");
    }

    public function getExchangeDate(): ?DateTimeInterface
    {
        return $this->exchangeDate ?? $this->resolveExchangeDate();
    }

    /**
     * @return $this|ExchangeSubjectInterface
     */
    public function setExchangeDate(?DateTimeInterface $date): ExchangeSubjectInterface
    {
        throw new LogicException("Supplier order's exchange date is resolved payments.");
    }

    private function resolveExchangeDate(): ?DateTimeInterface
    {
        return $this->getFirstValidCapturedPayment()?->getExchangeDate();
    }

    private function resolveExchangeRate(): ?Decimal
    {
        return $this->getFirstValidCapturedPayment()?->getExchangeRate();
    }

    private function getFirstValidCapturedPayment(): ?Model\SupplierPaymentInterface
    {
        foreach ($this->getPayments(false) as $payment) {
            if (PaymentStates::STATE_CAPTURED !== $payment->getState()) {
                continue;
            }

            return $payment;
        }

        return null;
    }
}
