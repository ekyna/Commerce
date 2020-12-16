<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
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
    use Common\IdentityTrait,
        Common\KeySubjectTrait,
        Common\NumberSubjectTrait,
        Common\StateSubjectTrait,
        Common\AdjustableTrait,
        Common\NotifiableTrait,
        Payment\PaymentSubjectTrait,
        Payment\PaymentTermSubjectTrait,
        Shipment\ShippableTrait,
        VatNumberSubjectTrait,
        RM\TimestampableTrait,
        RM\LocalizedTrait;


    /**
     * @var int
     */
    protected $id;

    /**
     * @var CustomerInterface
     */
    protected $customer;

    /**
     * @var CustomerGroupInterface
     */
    protected $customerGroup;

    /**
     * @var string
     */
    protected $company;

    /**
     * @var string
     */
    protected $companyNumber;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var Common\AddressInterface
     */
    protected $invoiceAddress;

    /**
     * @var Common\AddressInterface
     */
    protected $deliveryAddress;

    /**
     * @var bool
     */
    protected $sameAddress;

    /**
     * @var Common\CouponInterface
     */
    protected $coupon;

    /**
     * @var array
     */
    protected $couponData;

    /**
     * @var bool
     */
    protected $autoDiscount;

    /**
     * @var bool
     */
    protected $taxExempt;

    /**
     * @var string
     */
    protected $vatDisplayMode;

    /**
     * @var float
     */
    protected $netTotal;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $voucherNumber;

    /**
     * @var string
     */
    protected $originNumber;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $preparationNote;

    /**
     * @var string
     */
    protected $comment;

    /**
     * @var string
     */
    protected $documentComment;

    /**
     * @var DateTime
     */
    protected $acceptedAt;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var ArrayCollection|Common\SaleAttachmentInterface[]
     */
    protected $attachments;

    /**
     * @var ArrayCollection|Common\SaleItemInterface[]
     */
    protected $items;

    /**
     * @var ContextInterface
     */
    private $context;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->sameAddress = true;
        $this->autoDiscount = true;
        $this->taxExempt = false;

        $this->netTotal = 0;

        $this->createdAt = new DateTime();

        $this->attachments = new ArrayCollection();
        $this->items = new ArrayCollection();

        $this->initializeAdjustments();
        $this->initializeNotifications();
        $this->initializePaymentSubject();
        $this->initializeShippable();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->number ?: 'New sale';
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
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @inheritdoc
     */
    public function setCustomer(CustomerInterface $customer = null)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroup(CustomerGroupInterface $customerGroup = null)
    {
        $this->customerGroup = $customerGroup;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @inheritdoc
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCompanyNumber(): ?string
    {
        return $this->companyNumber;
    }

    /**
     * @inheritdoc
     */
    public function setCompanyNumber(string $number = null): Common\SaleInterface
    {
        $this->companyNumber = $number;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @inheritdoc
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isAutoDiscount()
    {
        return $this->autoDiscount;
    }

    /**
     * @inheritdoc
     */
    public function setAutoDiscount($auto)
    {
        $this->autoDiscount = (bool)$auto;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isTaxExempt()
    {
        return $this->taxExempt;
    }

    /**
     * @inheritdoc
     */
    public function setTaxExempt($exempt)
    {
        $this->taxExempt = $exempt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVatDisplayMode()
    {
        return $this->vatDisplayMode;
    }

    /**
     * @inheritdoc
     */
    public function setVatDisplayMode($mode)
    {
        $this->vatDisplayMode = $mode;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isAtiDisplayMode()
    {
        return $this->vatDisplayMode === VatDisplayModes::MODE_ATI;
    }

    /**
     * @inheritDoc
     */
    public function isSample()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isReleased()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getNetTotal()
    {
        return $this->netTotal;
    }

    /**
     * @inheritdoc
     */
    public function setNetTotal($total)
    {
        $this->netTotal = $total;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVoucherNumber()
    {
        return $this->voucherNumber;
    }

    /**
     * @inheritdoc
     */
    public function setVoucherNumber($number)
    {
        $this->voucherNumber = $number;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOriginNumber()
    {
        return $this->originNumber;
    }

    /**
     * @inheritdoc
     */
    public function setOriginNumber($number)
    {
        $this->originNumber = $number;

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
    public function getPreparationNote()
    {
        return $this->preparationNote;
    }

    /**
     * @inheritdoc
     */
    public function setPreparationNote($note)
    {
        $this->preparationNote = $note;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @inheritdoc
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Returns the document comment.
     *
     * @return string
     */
    public function getDocumentComment()
    {
        return $this->documentComment;
    }

    /**
     * Sets the document comment.
     *
     * @param string $comment
     *
     * @return AbstractSale
     */
    public function setDocumentComment($comment)
    {
        $this->documentComment = $comment;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAcceptedAt()
    {
        return $this->acceptedAt;
    }

    /**
     * @inheritdoc
     */
    public function setAcceptedAt(DateTime $acceptedAt = null)
    {
        $this->acceptedAt = $acceptedAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @inheritdoc
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasAttachments()
    {
        return 0 < $this->attachments->count();
    }

    /**
     * @inheritdoc
     */
    public function getAttachments()
    {
        return $this->attachments;
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
     * @inheritDoc
     */
    public function getDeliveryCountry()
    {
        if ($relayPoint = $this->getRelayPoint()) {
            return $relayPoint->getCountry();
        }

        $address = $this->isSameAddress() ? $this->getInvoiceAddress() : $this->getDeliveryAddress();

        return null !== $address ? $address->getCountry() : null;
    }

    /**
     * @inheritdoc
     */
    public function isSameAddress()
    {
        return $this->sameAddress;
    }

    /**
     * @inheritdoc
     */
    public function setSameAddress($sameAddress)
    {
        $this->sameAddress = (bool)$sameAddress;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCoupon(): ?Common\CouponInterface
    {
        return $this->coupon;
    }

    /**
     * @inheritDoc
     */
    public function setCoupon(Common\CouponInterface $coupon = null): Common\SaleInterface
    {
        $this->coupon = $coupon;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCouponData(): ?array
    {
        return $this->couponData;
    }

    /**
     * @inheritDoc
     */
    public function setCouponData(array $data = null): Common\SaleInterface
    {
        $this->couponData = $data;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getContext(): ?ContextInterface
    {
        return $this->context;
    }

    /**
     * @inheritdoc
     */
    public function setContext(ContextInterface $context): Common\SaleInterface
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isLocked(): bool
    {
        foreach ($this->payments as $payment) {
            if ($payment->getState() === Payment\PaymentStates::STATE_NEW) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function canBeReleased(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
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
