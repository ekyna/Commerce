<?php

namespace Ekyna\Component\Commerce\Customer\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Customer\Model as Model;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Pricing\Model\VatNumberSubjectTrait;
use Ekyna\Component\Resource\Model as RM;
use libphonenumber\PhoneNumber;

/**
 * Class Customer
 * @package Ekyna\Component\Commerce\Customer\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Customer implements Model\CustomerInterface
{
    use Common\IdentityTrait,
        Common\KeySubjectTrait,
        Common\NumberSubjectTrait,
        Common\CurrencySubjectTrait,
        Payment\PaymentTermSubjectTrait,
        Model\NotificationsTrait,
        VatNumberSubjectTrait,
        RM\LocalizedTrait,
        RM\TimestampableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $secret;

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
     * @var PhoneNumber
     */
    protected $phone;

    /**
     * @var PhoneNumber
     */
    protected $mobile;

    /**
     * @var DateTime
     */
    protected $birthday;

    /**
     * @var Model\CustomerInterface
     */
    protected $parent;

    /**
     * @var Collection|Model\CustomerInterface[]
     */
    protected $children;

    /**
     * @var Model\CustomerGroupInterface
     */
    protected $customerGroup;

    /**
     * @var Collection|Model\CustomerAddressInterface[]
     */
    protected $addresses;

    /**
     * @var Collection|Model\CustomerAddressInterface[]
     */
    protected $contacts;

    /**
     * @var Payment\PaymentMethodInterface
     */
    protected $defaultPaymentMethod;

    /**
     * @var Collection|Payment\PaymentMethodInterface[]
     */
    protected $paymentMethods;

    /**
     * @var CustomerLogo
     */
    protected $brandLogo;

    /**
     * @var string
     */
    protected $brandColor;

    /**
     * @var string
     */
    protected $brandUrl;

    /**
     * @var string
     */
    protected $documentFooter;

    /**
     * @var string[]
     * @see \Ekyna\Component\Commerce\Document\Model\DocumentTypes
     */
    protected $documentTypes;

    /**
     * @var int
     */
    protected $loyaltyPoints;

    /**
     * @var float
     */
    protected $creditBalance;

    /**
     * @var float
     */
    protected $outstandingLimit;

    /**
     * @var bool
     */
    protected $outstandingOverflow;

    /**
     * @var float
     */
    protected $outstandingBalance;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var string
     */
    protected $description;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->documentTypes = [];
        $this->loyaltyPoints = 0;
        $this->creditBalance = 0;
        $this->outstandingLimit = 0;
        $this->outstandingOverflow = false;
        $this->outstandingBalance = 0;

        $this->state = Model\CustomerStates::STATE_NEW;

        $this->notifications = [
            Common\NotificationTypes::CART_REMIND,
            Common\NotificationTypes::ORDER_ACCEPTED,
            Common\NotificationTypes::QUOTE_REMIND,
            Common\NotificationTypes::PAYMENT_AUTHORIZED,
            Common\NotificationTypes::PAYMENT_CAPTURED,
            Common\NotificationTypes::PAYMENT_PAYEDOUT,
            Common\NotificationTypes::PAYMENT_EXPIRED,
            Common\NotificationTypes::SHIPMENT_READY,
            Common\NotificationTypes::SHIPMENT_COMPLETE,
            Common\NotificationTypes::SHIPMENT_PARTIAL,
            Common\NotificationTypes::INVOICE_COMPLETE,
            Common\NotificationTypes::INVOICE_PARTIAL,
            Common\NotificationTypes::RETURN_PENDING,
            Common\NotificationTypes::RETURN_RECEIVED,
        ];

        $this->children = new ArrayCollection();
        $this->addresses = new ArrayCollection();
        $this->paymentMethods = new ArrayCollection();

        $this->createdAt = new DateTime();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->company) {
            $sign = '';
            if ($this->hasParent()) {
                $sign = '♦'; //'&loz;';
            } elseif ($this->hasChildren()) { // TODO Greedy : triggers collection initialization
                $sign = '◊'; //'&diams;';
            }

            return trim(sprintf('%s [%s] %s %s', $sign, $this->company, $this->firstName, $this->lastName));
        }

        return sprintf('%s %s', $this->firstName, $this->lastName);
    }

    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @inheritdoc
     */
    public function setCompany(string $company = null): Model\CustomerInterface
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
    public function setCompanyNumber(string $number = null): Model\CustomerInterface
    {
        $this->companyNumber = $number;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @inheritdoc
     */
    public function setEmail(string $email = null): Model\CustomerInterface
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    /**
     * @inheritdoc
     */
    public function setPhone(PhoneNumber $phone = null): Model\CustomerInterface
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMobile(): ?PhoneNumber
    {
        return $this->mobile;
    }

    /**
     * @inheritdoc
     */
    public function setMobile(PhoneNumber $mobile = null): Model\CustomerInterface
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBirthday(): ?DateTime
    {
        return $this->birthday;
    }

    /**
     * @inheritdoc
     */
    public function setBirthday(DateTime $birthday = null): Model\CustomerInterface
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasParent(): bool
    {
        return null !== $this->parent;
    }

    /**
     * @inheritdoc
     */
    public function getParent(): ?Model\CustomerInterface
    {
        return $this->parent;
    }

    /**
     * @inheritdoc
     */
    public function setParent(Model\CustomerInterface $parent = null): Model\CustomerInterface
    {
        if ($parent !== $this->parent) {
            if ($previous = $this->parent) {
                $previous = null;
                $this->parent->removeChild($this);
            }

            if ($this->parent = $parent) {
                $parent->addChild($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @inheritdoc
     */
    public function hasChild(Model\CustomerInterface $child): bool
    {
        return $this->children->contains($child);
    }

    /**
     * @inheritdoc
     */
    public function addChild(Model\CustomerInterface $child): Model\CustomerInterface
    {
        if (!$this->hasChild($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeChild(Model\CustomerInterface $child): Model\CustomerInterface
    {
        if ($this->hasChild($child)) {
            $this->children->removeElement($child);
            $child->setParent(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasChildren(): bool
    {
        return !$this->children->isEmpty();
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroup(): ?Model\CustomerGroupInterface
    {
        return $this->customerGroup;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroup(Model\CustomerGroupInterface $group = null): Model\CustomerInterface
    {
        $this->customerGroup = $group;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    /**
     * @inheritdoc
     */
    public function hasAddress(Model\CustomerAddressInterface $address): bool
    {
        return $this->addresses->contains($address);
    }

    /**
     * @inheritdoc
     */
    public function addAddress(Model\CustomerAddressInterface $address): Model\CustomerInterface
    {
        if (!$this->hasAddress($address)) {
            $this->addresses->add($address);
            $address->setCustomer($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAddress(Model\CustomerAddressInterface $address): Model\CustomerInterface
    {
        if ($this->hasAddress($address)) {
            $this->addresses->removeElement($address);
            $address->setCustomer(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    /**
     * @inheritdoc
     */
    public function hasContact(CustomerContact $contact): bool
    {
        return $this->contacts->contains($contact);
    }

    /**
     * @inheritdoc
     */
    public function addContact(CustomerContact $contact): Model\CustomerInterface
    {
        if (!$this->hasContact($contact)) {
            $this->contacts->add($contact);
            $contact->setCustomer($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeContact(CustomerContact $contact): Model\CustomerInterface
    {
        if ($this->hasContact($contact)) {
            $this->contacts->removeElement($contact);
            $contact->setCustomer(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultPaymentMethod(): ?Payment\PaymentMethodInterface
    {
        return $this->defaultPaymentMethod;
    }

    /**
     * @inheritdoc
     */
    public function setDefaultPaymentMethod(Payment\PaymentMethodInterface $method = null): Model\CustomerInterface
    {
        $this->defaultPaymentMethod = $method;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentMethods(): Collection
    {
        return $this->paymentMethods;
    }

    /**
     * @inheritdoc
     */
    public function hasPaymentMethod(Payment\PaymentMethodInterface $method): bool
    {
        return $this->paymentMethods->contains($method);
    }

    /**
     * @inheritdoc
     */
    public function addPaymentMethod(Payment\PaymentMethodInterface $method): Model\CustomerInterface
    {
        if (!$this->hasPaymentMethod($method)) {
            $this->paymentMethods->add($method);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removePaymentMethod(Payment\PaymentMethodInterface $method): Model\CustomerInterface
    {
        if ($this->hasPaymentMethod($method)) {
            $this->paymentMethods->removeElement($method);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBrandLogo(): ?CustomerLogo
    {
        return $this->brandLogo;
    }

    /**
     * @inheritDoc
     */
    public function setBrandLogo(CustomerLogo $logo = null): Model\CustomerInterface
    {
        if ($logo !== $this->brandLogo) {
            if ($this->brandLogo) {
                $this->brandLogo->setCustomer(null);
            }

            $this->brandLogo = $logo;

            $this->brandLogo->setCustomer($this);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBrandColor(): ?string
    {
        return $this->brandColor;
    }

    /**
     * @inheritDoc
     */
    public function setBrandColor(string $color = null): Model\CustomerInterface
    {
        $this->brandColor = $color;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBrandUrl(): ?string
    {
        return $this->brandUrl;
    }

    /**
     * @inheritDoc
     */
    public function setBrandUrl(string $url = null): Model\CustomerInterface
    {
        $this->brandUrl = $url;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDocumentFooter(): ?string
    {
        return $this->documentFooter;
    }

    /**
     * @inheritDoc
     */
    public function setDocumentFooter(string $html = null): Model\CustomerInterface
    {
        $this->documentFooter = $html;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDocumentTypes(): array
    {
        return $this->documentTypes;
    }

    /**
     * @inheritDoc
     */
    public function setDocumentTypes(array $types): Model\CustomerInterface
    {
        $this->documentTypes = [];

        foreach (array_unique($types) as $type) {
            if (DocumentTypes::isValid($type, false)) {
                continue;
            }

            $this->documentTypes[] = $type;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLoyaltyPoints(): int
    {
        return $this->loyaltyPoints;
    }

    /**
     * @inheritDoc
     */
    public function setLoyaltyPoints(int $points): Model\CustomerInterface
    {
        $this->loyaltyPoints = $points;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreditBalance(): float
    {
        return $this->creditBalance;
    }

    /**
     * @inheritdoc
     */
    public function setCreditBalance(float $creditBalance): Model\CustomerInterface
    {
        $this->creditBalance = $creditBalance;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOutstandingLimit(): float
    {
        return $this->outstandingLimit;
    }

    /**
     * @inheritdoc
     */
    public function setOutstandingLimit(float $limit): Model\CustomerInterface
    {
        $this->outstandingLimit = $limit;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isOutstandingOverflow(): bool
    {
        return $this->outstandingOverflow;
    }

    /**
     * @inheritdoc
     */
    public function setOutstandingOverflow(bool $overflow): Model\CustomerInterface
    {
        $this->outstandingOverflow = $overflow;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOutstandingBalance(): float
    {
        return $this->outstandingBalance;
    }

    /**
     * @inheritdoc
     */
    public function setOutstandingBalance(float $amount): Model\CustomerInterface
    {
        $this->outstandingBalance = $amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @inheritdoc
     */
    public function setState(string $state): Model\CustomerInterface
    {
        $this->state = $state;

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
    public function setDescription(string $description = null): Model\CustomerInterface
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultInvoiceAddress(bool $allowParentAddress = false): ?Model\CustomerAddressInterface
    {
        if ($allowParentAddress && $this->hasParent()) {
            if (null !== $address = $this->parent->getDefaultInvoiceAddress($allowParentAddress)) {
                return $address;
            }
        }

        if (null !== $address = $this->findOneAddressBy(Criteria::expr()->eq('invoiceDefault', true))) {
            return $address;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultDeliveryAddress(bool $allowParentAddress = false): ?Model\CustomerAddressInterface
    {
        if ($allowParentAddress && $this->hasParent()) {
            if (null !== $address = $this->parent->getDefaultDeliveryAddress($allowParentAddress)) {
                return $address;
            }
        }

        if (null !== $address = $this->findOneAddressBy(Criteria::expr()->eq('deliveryDefault', true))) {
            return $address;
        }

        return null;
    }

    /**
     * Finds one address by expression.
     *
     * @param mixed $expression
     *
     * @return Model\CustomerAddressInterface|null
     */
    private function findOneAddressBy($expression): ?Model\CustomerAddressInterface
    {
        if (0 < $this->addresses->count()) {
            $criteria = Criteria::create()
                ->where($expression)
                ->setMaxResults(1);

            $matches = $this->addresses->matching($criteria);
            if ($matches->count() == 1) {
                return $matches->first();
            }
        }

        return null;
    }
}
