<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Calculator\Amount;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Pricing\Model\VatNumberSubjectTrait;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class AbstractSale
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSale implements Common\SaleInterface
{
    use Common\IdentityTrait,
        Common\KeySubjectTrait,
        Common\NumberSubjectTrait,
        Common\StateSubjectTrait,
        Common\AdjustableTrait,
        Payment\PaymentSubjectTrait,
        Payment\PaymentTermSubjectTrait,
        Shipment\ShipmentDataTrait,
        VatNumberSubjectTrait,
        TimestampableTrait;

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
     * @var bool
     */
    protected $autoDiscount;

    /**
     * @var bool
     */
    protected $taxExempt;

    /**
     * @var float
     */
    protected $netTotal;

    /**
     * @var float
     */
    protected $adjustmentTotal;

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
    protected $comment;

    /**
     * @var ArrayCollection|Common\SaleAttachmentInterface[]
     */
    protected $attachments;

    /**
     * @var ArrayCollection|Common\SaleItemInterface[]
     */
    protected $items;

    /**
     * @var Amount
     */
    private $grossResult;

    /**
     * @var Amount
     */
    private $shipmentResult;

    /**
     * @var Amount
     */
    private $finalResult;


    /**
     * Constructor.
     */
    public function __construct()
    {
        if (null === $this->state) {
            throw new RuntimeException("Initial state must be defined.");
        }

        $this->sameAddress = true;
        $this->autoDiscount = true;
        $this->taxExempt = false;

        $this->netTotal = 0;
        $this->adjustmentTotal = 0;

        $this->createdAt = new \DateTime();

        $this->attachments = new ArrayCollection();
        $this->items = new ArrayCollection();

        $this->initializeAdjustments();
        $this->initializePaymentSubject();
        $this->initializeShipmentData();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return empty($this->number) ? 'New sale' : $this->number;
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
     * @inheritDoc
     */
    public function isSample()
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
    public function getAdjustmentTotal()
    {
        return $this->adjustmentTotal;
    }

    /**
     * @inheritdoc
     */
    public function setAdjustmentTotal($total)
    {
        $this->adjustmentTotal = $total;

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
     * @inheritdoc
     */
    public function clearResults()
    {
        foreach ($this->items as $item) {
            $item->clearResult();
        }

        /** @var Common\SaleAdjustmentInterface $adjustment */
        foreach ($this->adjustments as $adjustment) {
            $adjustment->clearResult();
        }

        $this->grossResult = null;
        $this->shipmentResult = null;
        $this->finalResult = null;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setGrossResult(Amount $result)
    {
        $this->grossResult = $result;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGrossResult()
    {
        return $this->grossResult;
    }

    /**
     * @inheritdoc
     */
    public function setShipmentResult(Amount $result)
    {
        $this->shipmentResult = $result;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShipmentResult()
    {
        return $this->shipmentResult;
    }

    /**
     * @inheritdoc
     */
    public function setFinalResult(Amount $result)
    {
        $this->finalResult = $result;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFinalResult()
    {
        return $this->finalResult;
    }
}
