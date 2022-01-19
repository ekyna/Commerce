<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Pricing\Model\VatDisplayModes;
use Ekyna\Component\Commerce\Pricing\Model\VatNumberSubjectTrait;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\Resource\Model as RM;

/**
 * Class AbstractSale
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * Any mapped property (from traits too) must be reported into the SaleCopier.
 * @see     \Ekyna\Component\Commerce\Common\Transformer\SaleCopier
 */
abstract class AbstractSale implements Common\SaleInterface
{
    use Common\AdjustableTrait;
    use Common\IdentityTrait;
    use Common\KeySubjectTrait;
    use Common\NotifiableTrait;
    use Common\NumberSubjectTrait;
    use Common\StateSubjectTrait;
    use Payment\PaymentSubjectTrait;
    use Payment\PaymentTermSubjectTrait;
    use RM\LocalizedTrait;
    use RM\TimestampableTrait;
    use RM\RuntimeUidTrait;
    use Shipment\ShippableTrait;
    use VatNumberSubjectTrait;


    protected ?int                         $id              = null;
    protected ?CustomerInterface           $customer        = null;
    protected ?CustomerGroupInterface      $customerGroup   = null;
    protected ?string                      $company         = null;
    protected ?string                      $companyNumber   = null;
    protected ?string                      $email           = null;
    protected ?Common\SaleAddressInterface $invoiceAddress  = null;
    protected ?Common\SaleAddressInterface $deliveryAddress = null;
    protected bool                         $sameAddress     = true;
    protected ?Common\CouponInterface      $coupon          = null;
    protected ?array                       $couponData      = null;
    protected bool                         $autoDiscount    = true;
    protected bool                         $taxExempt       = false;
    protected ?string                      $vatDisplayMode  = null;
    protected Decimal                      $netTotal;
    protected ?string                      $title           = null;
    protected ?string                      $voucherNumber   = null;
    protected ?string                      $originNumber    = null;
    protected ?string                      $description     = null;
    protected ?string                      $preparationNote = null;
    protected ?string                      $comment         = null;
    protected ?string                      $documentComment = null;
    protected ?DateTimeInterface           $acceptedAt      = null;
    protected string                       $source          = Common\SaleSources::SOURCE_WEBSITE;
    /** @var Collection|Common\SaleAttachmentInterface[] */
    protected Collection $attachments;
    /** @var ArrayCollection|Common\SaleItemInterface[] */
    protected Collection      $items;
    private ?ContextInterface $context = null;


    public function __construct()
    {
        $this->netTotal = new Decimal(0);

        $this->attachments = new ArrayCollection();
        $this->items = new ArrayCollection();

        $this->initializeTimestampable();
        $this->initializeAdjustments();
        $this->initializeNotifications();
        $this->initializePaymentSubject();
        $this->initializeShippable();
    }

    /**
     * Returns the string representation.
     */
    public function __toString(): string
    {
        return $this->number ?: 'New sale';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerInterface $customer): Common\SaleInterface
    {
        $this->customer = $customer;

        return $this;
    }

    public function getCustomerGroup(): ?CustomerGroupInterface
    {
        return $this->customerGroup;
    }

    public function setCustomerGroup(?CustomerGroupInterface $customerGroup): Common\SaleInterface
    {
        $this->customerGroup = $customerGroup;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): Common\SaleInterface
    {
        $this->company = $company;

        return $this;
    }

    public function getCompanyNumber(): ?string
    {
        return $this->companyNumber;
    }

    public function setCompanyNumber(?string $number): Common\SaleInterface
    {
        $this->companyNumber = $number;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): Common\SaleInterface
    {
        $this->email = $email;

        return $this;
    }

    public function isAutoDiscount(): bool
    {
        return $this->autoDiscount;
    }

    public function setAutoDiscount(bool $auto): Common\SaleInterface
    {
        $this->autoDiscount = $auto;

        return $this;
    }

    public function isTaxExempt(): bool
    {
        return $this->taxExempt;
    }

    public function setTaxExempt(bool $exempt): Common\SaleInterface
    {
        $this->taxExempt = $exempt;

        return $this;
    }

    public function getVatDisplayMode(): ?string
    {
        return $this->vatDisplayMode;
    }

    public function setVatDisplayMode(?string $mode): Common\SaleInterface
    {
        $this->vatDisplayMode = $mode;

        return $this;
    }

    public function isAtiDisplayMode(): bool
    {
        return $this->vatDisplayMode === VatDisplayModes::MODE_ATI;
    }

    public function isSample(): bool
    {
        return false;
    }

    public function isReleased(): bool
    {
        return false;
    }

    public function getNetTotal(): Decimal
    {
        return $this->netTotal;
    }

    public function setNetTotal(Decimal $total): Common\SaleInterface
    {
        $this->netTotal = $total;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): Common\SaleInterface
    {
        $this->title = $title;

        return $this;
    }

    public function getVoucherNumber(): ?string
    {
        return $this->voucherNumber;
    }

    public function setVoucherNumber(?string $number): Common\SaleInterface
    {
        $this->voucherNumber = $number;

        return $this;
    }

    public function getOriginNumber(): ?string
    {
        return $this->originNumber;
    }

    public function setOriginNumber(?string $number): Common\SaleInterface
    {
        $this->originNumber = $number;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): Common\SaleInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getPreparationNote(): ?string
    {
        return $this->preparationNote;
    }

    public function setPreparationNote(?string $note): Common\SaleInterface
    {
        $this->preparationNote = $note;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): Common\SaleInterface
    {
        $this->comment = $comment;

        return $this;
    }

    public function getDocumentComment(): ?string
    {
        return $this->documentComment;
    }

    public function setDocumentComment(?string $comment): Common\SaleInterface
    {
        $this->documentComment = $comment;

        return $this;
    }

    public function getAcceptedAt(): ?DateTimeInterface
    {
        return $this->acceptedAt;
    }

    public function setAcceptedAt(?DateTimeInterface $date): Common\SaleInterface
    {
        $this->acceptedAt = $date;

        return $this;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): Common\SaleInterface
    {
        $this->source = $source;

        return $this;
    }

    public function hasAttachments(): bool
    {
        return 0 < $this->attachments->count();
    }

    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function hasItems(): bool
    {
        return 0 < $this->items->count();
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function getDeliveryCountry(): ?Common\CountryInterface
    {
        if ($relayPoint = $this->getRelayPoint()) {
            return $relayPoint->getCountry();
        }

        $address = $this->isSameAddress() ? $this->getInvoiceAddress() : $this->getDeliveryAddress();

        return null !== $address ? $address->getCountry() : null;
    }

    public function isSameAddress(): bool
    {
        return $this->sameAddress;
    }

    public function setSameAddress(bool $same): Common\SaleInterface
    {
        $this->sameAddress = $same;

        return $this;
    }

    public function getCoupon(): ?Common\CouponInterface
    {
        return $this->coupon;
    }

    public function setCoupon(?Common\CouponInterface $coupon): Common\SaleInterface
    {
        $this->coupon = $coupon;

        return $this;
    }

    public function getCouponData(): ?array
    {
        return $this->couponData;
    }

    public function setCouponData(?array $data): Common\SaleInterface
    {
        $this->couponData = $data;

        return $this;
    }

    public function getContext(): ?ContextInterface
    {
        return $this->context;
    }

    public function setContext(?ContextInterface $context): Common\SaleInterface
    {
        $this->context = $context;

        return $this;
    }

    public function isLocked(): bool
    {
        foreach ($this->payments as $payment) {
            if ($payment->getState() === Payment\PaymentStates::STATE_NEW) {
                return true;
            }
        }

        return false;
    }

    public function canBeReleased(): bool
    {
        return false;
    }

    public function hasDiscountItemAdjustment(array $items = null): bool
    {
        $items = $items ?? $this->items;

        foreach ($items as $item) {
            if ($item->hasAdjustments(Common\AdjustmentTypes::TYPE_DISCOUNT)) {
                return true;
            }

            if ($item->hasChildren() && $this->hasDiscountItemAdjustment($item->getChildren()->toArray())) {
                return true;
            }
        }

        return false;
    }
}
