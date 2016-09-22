<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Payment\Model as Payment;

/**
 * Class AbstractSale
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSale extends AbstractAdjustable implements Model\SaleInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $number;

    /**
     * @var CustomerInterface
     */
    protected $customer;

    /**
     * @var string
     */
    protected $company;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var Model\AddressInterface
     */
    protected $invoiceAddress;

    /**
     * @var Model\AddressInterface
     */
    protected $deliveryAddress;

    /**
     * @var bool
     */
    protected $sameAddress;

    /**
     * @var Model\CurrencyInterface
     */
    protected $currency;

    /**
     * @var float
     */
    protected $weightTotal;

    /**
     * @var float
     */
    protected $netTotal;

    /**
     * @var float
     */
    protected $adjustmentTotal;

    /**
     * @var float
     */
    protected $grandTotal;

    /**
     * @var float
     */
    protected $paidTotal;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var string
     */
    protected $paymentState;

    /**
     * @var ArrayCollection|Model\SaleItemInterface[]
     */
    protected $items;

    /**
     * @var ArrayCollection|Payment\PaymentInterface[]
     */
    protected $payments;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (null === $this->state) {
            throw new RuntimeException("Initial state must be defined.");
        }

        $this->sameAddress = true;

        $this->weightTotal = 0;
        $this->netTotal = 0;
        $this->adjustmentTotal = 0;
        $this->grandTotal = 0;
        $this->paidTotal = 0;

        $this->paymentState = Payment\PaymentStates::STATE_NEW;

        $this->items = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     *
     * @return string
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
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @inheritdoc
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
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
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @inheritdoc
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @inheritdoc
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

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
    public function getInvoiceAddress()
    {
        return $this->invoiceAddress;
    }

    /**
     * @inheritdoc
     */
    public function setInvoiceAddress(Model\AddressInterface $address)
    {
        $this->validateAddressClass($address);

        $this->invoiceAddress = $address;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryAddress(Model\AddressInterface $address = null)
    {
        if (null !== $address) {
            $this->validateAddressClass($address);
        }

        $this->deliveryAddress = $address;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSameAddress()
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
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @inheritdoc
     */
    public function setCurrency(Model\CurrencyInterface $currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWeightTotal()
    {
        return $this->weightTotal;
    }

    /**
     * @inheritdoc
     */
    public function setWeightTotal($weightTotal)
    {
        $this->weightTotal = $weightTotal;

        return $this;
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
    public function setNetTotal($netTotal)
    {
        $this->netTotal = $netTotal;

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
    public function setAdjustmentTotal($adjustmentTotal)
    {
        $this->adjustmentTotal = $adjustmentTotal;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGrandTotal()
    {
        return $this->grandTotal;
    }

    /**
     * @inheritdoc
     */
    public function setGrandTotal($grandTotal)
    {
        $this->grandTotal = $grandTotal;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPaidTotal()
    {
        return $this->paidTotal;
    }

    /**
     * @inheritdoc
     */
    public function setPaidTotal($paidTotal)
    {
        $this->paidTotal = $paidTotal;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentState($paymentState)
    {
        $this->paymentState = $paymentState;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentState()
    {
        return $this->paymentState;
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
    public function hasPayments()
    {
        return 0 < $this->payments->count();
    }

    /**
     * @inheritdoc
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Validates the address class.
     *
     * @param Model\AddressInterface $address
     *
     * @throws InvalidArgumentException
     */
    abstract protected function validateAddressClass(Model\AddressInterface $address);
}
