<?php

namespace Ekyna\Component\Commerce\Document\Builder;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Document\Model as Document;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Shipment\Model\RelayPointInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Intl\Intl;

/**
 * Class DocumentBuilder
 * @package Ekyna\Component\Commerce\Common\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentBuilder implements DocumentBuilderInterface
{
    /**
     * @var LocaleProviderInterface
     */
    protected $localeProvider;

    /**
     * @var PhoneNumberUtil
     */
    private $phoneNumberUtil;


    /**
     * Constructor.
     *
     * @param LocaleProviderInterface $localeProvider
     * @param PhoneNumberUtil         $phoneNumberUtil
     */
    public function __construct(LocaleProviderInterface $localeProvider, PhoneNumberUtil $phoneNumberUtil = null)
    {
        $this->localeProvider = $localeProvider;
        $this->phoneNumberUtil = $phoneNumberUtil ?: PhoneNumberUtil::getInstance();
    }

    /**
     * @inheritDoc
     */
    public function build(Document\DocumentInterface $document)
    {
        if (null === $sale = $document->getSale()) {
            throw new LogicException("Document's sale must be set at this point.");
        }

        $this->update($document);

        // Goods lines
        $this->buildGoodsLines($document);

        // Discounts lines
        $this->buildDiscountsLines($document);

        // Shipment line
        $this->buildShipmentLine($document);
    }

    /**
     * @inheritdoc
     */
    public function update(Document\DocumentInterface $document)
    {
        if (null === $sale = $document->getSale()) {
            throw new LogicException("Invoice's sale must be set at this point.");
        }

        if (!$sale->getLocale()) {
            $sale->setLocale($this->localeProvider->getCurrentLocale());
        }

        $changed = false;

        // Locale
        $data = $sale->getLocale();
        if ($document->getLocale() !== $data) {
            $document->setLocale($data);
            $changed = true;
        }

        // Currency
        $data = $sale->getCurrency()->getCode();
        if ($document->getCurrency() !== $data) {
            $document->setCurrency($data);
            $changed = true;
        }

        // Customer
        $data = $this->buildCustomerData($sale);
        if ($document->getCustomer() !== $data) {
            $document->setCustomer($data);
            $changed = true;
        }

        // Invoice address
        $data = $this->buildAddressData($sale->getInvoiceAddress(), $document->getLocale());
        if ($document->getInvoiceAddress() !== $data) {
            $document->setInvoiceAddress($data);
            $changed = true;
        }

        // Delivery address
        $data = $sale->getDeliveryAddress()
            ? $this->buildAddressData($sale->getDeliveryAddress(), $document->getLocale())
            : null;
        if ($document->getDeliveryAddress() !== $data) {
            $document->setDeliveryAddress($data);
            $changed = true;
        }

        // RelayPoint
        if (null !== $data = $sale->getRelayPoint()) {
            $data = $this->buildAddressData($data, $document->getLocale());
        }
        if ($document->getRelayPoint() !== $data) {
            $document->setRelayPoint($data);
            $changed = true;
        }

        // Comment
        if (empty($document->getComment()) && !empty($comment = $sale->getDocumentComment())) {
            $document->setComment($comment);
            $changed = true;
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function buildGoodLine(Common\SaleItemInterface $item, Document\DocumentInterface $document)
    {
        $line = null;

        if ($item->isPrivate()) {
            return null;
        }

        // Skip compound with only public children
        if (!($item->isCompound() && !$item->hasPrivateChildren())) {
            // Existing line lookup
            foreach ($document->getLinesByType(Document\DocumentLineTypes::TYPE_GOOD) as $documentLine) {
                if ($documentLine->getSaleItem() === $item) {
                    $line = $documentLine;
                }
            }
            // Not found, create it
            if (null === $line) {
                $line = $this->createLine($document);
                $line
                    ->setDocument($document)
                    ->setType(Document\DocumentLineTypes::TYPE_GOOD)
                    ->setSaleItem($item)
                    ->setDesignation($item->getDesignation())
                    ->setDescription($item->getDescription())
                    ->setReference($item->getReference())
                    ->setQuantity($item->getTotalQuantity());
            }
        }

        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $this->buildGoodLine($child, $document);
            }
        }

        return $line;
    }

    /**
     * @inheritdoc
     */
    public function buildDiscountLine(Common\SaleAdjustmentInterface $adjustment, Document\DocumentInterface $document)
    {
        if ($adjustment->getType() !== Common\AdjustmentTypes::TYPE_DISCOUNT) {
            throw new InvalidArgumentException("Unexpected adjustment type.");
        }

        $line = null;
        // Existing line lookup
        foreach ($document->getLinesByType(Document\DocumentLineTypes::TYPE_DISCOUNT) as $documentLine) {
            if ($documentLine->getSaleAdjustment() === $adjustment) {
                $line = $documentLine;
            }
        }
        // Not found, create it
        if (null === $line) {
            $line = $this->createLine($document);
            $line
                ->setDocument($document)
                ->setType(Document\DocumentLineTypes::TYPE_DISCOUNT)
                ->setSaleAdjustment($adjustment)
                ->setDesignation($adjustment->getDesignation());
        }

        return $line;
    }

    /**
     * @inheritdoc
     */
    public function buildShipmentLine(Document\DocumentInterface $document)
    {
        $sale = $document->getSale();

        if (null === $method = $sale->getShipmentMethod()) {
            return null;
        }

        // Existing line lookup
        $shipmentLines = $document->getLinesByType(Document\DocumentLineTypes::TYPE_SHIPMENT);
        $line = !empty($shipmentLines) ? current($shipmentLines) : null;

        // Not found, create it
        if (null === $line) {
            $line = $this->createLine($document);
            $line
                ->setDocument($document)
                ->setType(Document\DocumentLineTypes::TYPE_SHIPMENT)
                ->setDesignation($method->getTitle());
        }

        return $line;
    }

    /**
     * Builds the document's customer data.
     *
     * @param Common\SaleInterface $sale
     *
     * @return array
     */
    protected function buildCustomerData(Common\SaleInterface $sale)
    {
        if (null !== $customer = $sale->getCustomer()) {
            return [
                'number'    => $customer->getNumber(),
                'company'   => $customer->getCompany(),
                'full_name' => trim($customer->getFirstName() . ' ' . $customer->getLastName()),
                'email'     => $customer->getEmail(),
                'phone'     => $this->formatPhoneNumber($customer->getPhone()),
                'mobile'    => $this->formatPhoneNumber($customer->getMobile()),
            ];
        } else {
            return [
                'number'    => null,
                'company'   => $sale->getCompany(),
                'full_name' => trim($sale->getFirstName() . ' ' . $sale->getLastName()),
                'email'     => $sale->getEmail(),
                'phone'     => null,
                'mobile'    => null,
            ];
        }
    }

    /**
     * Builds the document's address data.
     *
     * @param Common\AddressInterface $address
     * @param string                  $locale
     *
     * @return array
     */
    protected function buildAddressData(Common\AddressInterface $address, string $locale)
    {
        $country = Intl::getRegionBundle()->getCountryName($address->getCountry()->getCode(), $locale);

        $fullName = trim($address->getFirstName() . ' ' . $address->getLastName());

        $data = [
            'company'     => $address->getCompany(),
            'full_name'   => $fullName,
            'street'      => $address->getStreet(),
            'complement'  => $address->getComplement(),
            'supplement'  => $address->getSupplement(),
            'postal_code' => $address->getPostalCode(),
            'city'        => $address->getCity(),
            'country'     => $country,
            'state'       => '',
            'phone'       => $this->formatPhoneNumber($address->getPhone()),
            'mobile'      => $this->formatPhoneNumber($address->getMobile()),
        ];

        if ($address instanceof RelayPointInterface) {
            $data['number'] = $address->getNumber();
        }

        if ($address instanceof Common\SaleAddressInterface) {
            $data['information'] = $address->getInformation();
        }

        return $data;
    }

    /**
     * Formats the given phone number.
     *
     * @param PhoneNumber $number
     *
     * @return string
     */
    protected function formatPhoneNumber(PhoneNumber $number = null)
    {
        if ($number) {
            return $this->phoneNumberUtil->format($number, PhoneNumberFormat::INTERNATIONAL);
        }

        return null;
    }

    /**
     * Builds the document's goods lines.
     *
     * @param Document\DocumentInterface $document
     */
    protected function buildGoodsLines(Document\DocumentInterface $document)
    {
        foreach ($document->getSale()->getItems() as $item) {
            $this->buildGoodLine($item, $document);
        }
    }

    /**
     * Builds the document's discounts lines.
     *
     * @param Document\DocumentInterface $document
     */
    protected function buildDiscountsLines(Document\DocumentInterface $document)
    {
        $sale = $document->getSale();

        if (!$sale->hasAdjustments(Common\AdjustmentTypes::TYPE_DISCOUNT)) {
            return;
        }

        $adjustments = $sale->getAdjustments();
        foreach ($adjustments as $adjustment) {
            if ($adjustment->getType() === Common\AdjustmentTypes::TYPE_DISCOUNT) {
                $this->buildDiscountLine($adjustment, $document);
            }
        }
    }

    /**
     * Creates a new line.
     *
     * @param Document\DocumentInterface $document
     *
     * @return Document\DocumentLineInterface
     */
    protected function createLine(
        /** @noinspection PhpUnusedParameterInspection */
        Document\DocumentInterface $document
    ) {
        return new Document\DocumentLine();
    }
}
