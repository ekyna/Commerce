<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Entity;

use DateTime;
use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Customer\Model as Model;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Pricing\Model\VatNumberSubjectTrait;
use Ekyna\Component\Resource\Model as RM;
use Ekyna\Component\Resource\Model\AbstractResource;
use libphonenumber\PhoneNumber;

/**
 * Class Customer
 * @package Ekyna\Component\Commerce\Customer\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Customer extends AbstractResource implements Model\CustomerInterface
{
    use Common\CurrencySubjectTrait;
    use Common\IdentityTrait;
    use Common\KeySubjectTrait;
    use Common\NumberSubjectTrait;
    use Model\NotificationsTrait;
    use Payment\PaymentTermSubjectTrait;
    use RM\LocalizedTrait;
    use RM\TimestampableTrait;
    use VatNumberSubjectTrait;

    protected ?string                  $company          = null;
    protected ?string                  $companyNumber    = null;
    protected ?string                  $email            = null;
    protected ?PhoneNumber             $phone            = null;
    protected ?PhoneNumber             $mobile           = null;
    protected ?CustomerPosition        $customerPosition = null;
    protected ?DateTimeInterface       $birthday         = null;
    protected ?Model\CustomerInterface $parent           = null;
    /** @var Collection<Model\CustomerInterface> */
    protected Collection                    $children;
    protected ?Model\CustomerGroupInterface $customerGroup = null;
    /** @var Collection<Model\CustomerAddressInterface> */
    protected Collection $addresses;
    /** @var Collection<Model\CustomerAddressInterface> */
    protected Collection                      $contacts;
    protected ?Payment\PaymentMethodInterface $defaultPaymentMethod = null;
    /** @var Collection<Payment\PaymentMethodInterface> */
    protected Collection    $paymentMethods;
    protected ?CustomerLogo $brandLogo           = null;
    protected ?string       $brandColor          = null;
    protected ?string       $brandUrl            = null;
    protected ?string       $documentFooter      = null;
    protected int           $loyaltyPoints       = 0;
    protected Decimal       $creditBalance;
    protected Decimal       $outstandingLimit;
    protected bool          $outstandingOverflow = false;
    protected Decimal       $outstandingBalance;
    protected string        $state               = Model\CustomerStates::STATE_NEW;
    protected ?string       $description         = null;
    /**
     * @var array<string>
     * @see \Ekyna\Component\Commerce\Document\Model\DocumentTypes
     */
    protected array $documentTypes = [];


    public function __construct()
    {
        $this->creditBalance = new Decimal(0);
        $this->outstandingLimit = new Decimal(0);
        $this->outstandingBalance = new Decimal(0);

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

    public function __toString(): string
    {
        if (empty($this->firstName) && empty($this->lastName)) {
            return 'New customer';
        }

        if ($this->company) {
            $sign = '';
            if ($this->hasParent()) {
                $sign = '♦'; //'&loz;';
            } elseif ($this->hasChildren()) {
                $sign = '◊'; //'&diams;';
            }

            return trim(sprintf('%s [%s] %s %s', $sign, $this->company, $this->firstName, $this->lastName));
        }

        return trim(sprintf('%s %s', $this->firstName, $this->lastName));
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): Model\CustomerInterface
    {
        $this->company = $company;

        return $this;
    }

    public function getCompanyNumber(): ?string
    {
        return $this->companyNumber;
    }

    public function setCompanyNumber(?string $number): Model\CustomerInterface
    {
        $this->companyNumber = $number;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): Model\CustomerInterface
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function setPhone(?PhoneNumber $phone): Model\CustomerInterface
    {
        $this->phone = $phone;

        return $this;
    }

    public function getMobile(): ?PhoneNumber
    {
        return $this->mobile;
    }

    public function setMobile(?PhoneNumber $mobile): Model\CustomerInterface
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getCustomerPosition(): ?CustomerPosition
    {
        return $this->customerPosition;
    }

    public function setCustomerPosition(?CustomerPosition $position): Model\CustomerInterface
    {
        $this->customerPosition = $position;

        return $this;
    }

    public function getBirthday(): ?DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?DateTimeInterface $birthday): Model\CustomerInterface
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function hasParent(): bool
    {
        return null !== $this->parent;
    }

    public function getParent(): ?Model\CustomerInterface
    {
        return $this->parent;
    }

    public function setParent(?Model\CustomerInterface $parent): Model\CustomerInterface
    {
        if ($parent === $this->parent) {
            return $this;
        }

        if ($this->parent) {
            $this->parent->removeChild($this);
        }

        if ($this->parent = $parent) {
            $this->parent->addChild($this);
        }

        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function hasChild(Model\CustomerInterface $child): bool
    {
        return $this->children->contains($child);
    }

    public function addChild(Model\CustomerInterface $child): Model\CustomerInterface
    {
        if (!$this->hasChild($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(Model\CustomerInterface $child): Model\CustomerInterface
    {
        if ($this->hasChild($child)) {
            $this->children->removeElement($child);
            $child->setParent(null);
        }

        return $this;
    }

    public function hasChildren(): bool
    {
        return !$this->children->isEmpty();
    }

    public function getCustomerGroup(): ?Model\CustomerGroupInterface
    {
        return $this->customerGroup;
    }

    public function setCustomerGroup(?Model\CustomerGroupInterface $group): Model\CustomerInterface
    {
        $this->customerGroup = $group;

        return $this;
    }

    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function hasAddress(Model\CustomerAddressInterface $address): bool
    {
        return $this->addresses->contains($address);
    }

    public function addAddress(Model\CustomerAddressInterface $address): Model\CustomerInterface
    {
        if (!$this->hasAddress($address)) {
            $this->addresses->add($address);
            $address->setCustomer($this);
        }

        return $this;
    }

    public function removeAddress(Model\CustomerAddressInterface $address): Model\CustomerInterface
    {
        if ($this->hasAddress($address)) {
            $this->addresses->removeElement($address);
            $address->setCustomer(null);
        }

        return $this;
    }

    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function hasContact(CustomerContact $contact): bool
    {
        return $this->contacts->contains($contact);
    }

    public function addContact(CustomerContact $contact): Model\CustomerInterface
    {
        if (!$this->hasContact($contact)) {
            $this->contacts->add($contact);
            $contact->setCustomer($this);
        }

        return $this;
    }

    public function removeContact(CustomerContact $contact): Model\CustomerInterface
    {
        if ($this->hasContact($contact)) {
            $this->contacts->removeElement($contact);
            $contact->setCustomer(null);
        }

        return $this;
    }

    public function getDefaultPaymentMethod(): ?Payment\PaymentMethodInterface
    {
        return $this->defaultPaymentMethod;
    }

    public function setDefaultPaymentMethod(?Payment\PaymentMethodInterface $method): Model\CustomerInterface
    {
        $this->defaultPaymentMethod = $method;

        return $this;
    }

    public function getPaymentMethods(): Collection
    {
        return $this->paymentMethods;
    }

    public function hasPaymentMethod(Payment\PaymentMethodInterface $method): bool
    {
        return $this->paymentMethods->contains($method);
    }

    public function addPaymentMethod(Payment\PaymentMethodInterface $method): Model\CustomerInterface
    {
        if (!$this->hasPaymentMethod($method)) {
            $this->paymentMethods->add($method);
        }

        return $this;
    }

    public function removePaymentMethod(Payment\PaymentMethodInterface $method): Model\CustomerInterface
    {
        if ($this->hasPaymentMethod($method)) {
            $this->paymentMethods->removeElement($method);
        }

        return $this;
    }

    public function getBrandLogo(): ?CustomerLogo
    {
        return $this->brandLogo;
    }

    public function setBrandLogo(?CustomerLogo $logo): Model\CustomerInterface
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

    public function getBrandColor(): ?string
    {
        return $this->brandColor;
    }

    public function setBrandColor(?string $color): Model\CustomerInterface
    {
        $this->brandColor = $color;

        return $this;
    }

    public function getBrandUrl(): ?string
    {
        return $this->brandUrl;
    }

    public function setBrandUrl(?string $url): Model\CustomerInterface
    {
        $this->brandUrl = $url;

        return $this;
    }

    public function getDocumentFooter(): ?string
    {
        return $this->documentFooter;
    }

    public function setDocumentFooter(?string $html): Model\CustomerInterface
    {
        $this->documentFooter = $html;

        return $this;
    }

    public function getDocumentTypes(): array
    {
        return $this->documentTypes;
    }

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

    public function getLoyaltyPoints(): int
    {
        return $this->loyaltyPoints;
    }

    public function setLoyaltyPoints(int $points): Model\CustomerInterface
    {
        $this->loyaltyPoints = $points;

        return $this;
    }

    public function getCreditBalance(): Decimal
    {
        return $this->creditBalance;
    }

    public function setCreditBalance(Decimal $creditBalance): Model\CustomerInterface
    {
        $this->creditBalance = $creditBalance;

        return $this;
    }

    public function getOutstandingLimit(): Decimal
    {
        return $this->outstandingLimit;
    }

    public function setOutstandingLimit(Decimal $limit): Model\CustomerInterface
    {
        $this->outstandingLimit = $limit;

        return $this;
    }

    public function isOutstandingOverflow(): bool
    {
        return $this->outstandingOverflow;
    }

    public function setOutstandingOverflow(bool $overflow): Model\CustomerInterface
    {
        $this->outstandingOverflow = $overflow;

        return $this;
    }

    public function getOutstandingBalance(): Decimal
    {
        return $this->outstandingBalance;
    }

    public function setOutstandingBalance(Decimal $amount): Model\CustomerInterface
    {
        $this->outstandingBalance = $amount;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): Model\CustomerInterface
    {
        $this->state = $state;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description = null): Model\CustomerInterface
    {
        $this->description = $description;

        return $this;
    }

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
     */
    private function findOneAddressBy($expression): ?Model\CustomerAddressInterface
    {
        if (0 < $this->addresses->count()) {
            $criteria = Criteria::create()
                ->where($expression)
                ->setMaxResults(1);

            $matches = $this->addresses->matching($criteria);
            if (1 === $matches->count()) {
                return $matches->first();
            }
        }

        return null;
    }
}
