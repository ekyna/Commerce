<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Document\Builder;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\Transformer\ArrayToAddressTransformer;
use Ekyna\Component\Commerce\Document\Model as Document;
use Ekyna\Component\Commerce\Document\Util\DocumentUtil;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * Class DocumentBuilder
 * @package Ekyna\Component\Commerce\Common\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentBuilder implements DocumentBuilderInterface
{
    protected LocaleProviderInterface   $localeProvider;
    protected ArrayToAddressTransformer $addressTransformer;
    private PhoneNumberUtil             $phoneNumberUtil;

    public function __construct(
        LocaleProviderInterface   $localeProvider,
        ArrayToAddressTransformer $addressTransformer,
        PhoneNumberUtil           $phoneNumberUtil = null
    ) {
        $this->localeProvider = $localeProvider;
        $this->addressTransformer = $addressTransformer;
        $this->phoneNumberUtil = $phoneNumberUtil ?: PhoneNumberUtil::getInstance();
    }

    public function build(Document\DocumentInterface $document): void
    {
        if (null === $document->getSale()) {
            throw new LogicException('Document\'s sale must be set at this point.');
        }

        $this->update($document);

        // Goods lines
        $this->buildGoodsLines($document);

        // Discounts lines
        $this->buildDiscountsLines($document);

        // Shipment line
        $this->buildShipmentLine($document);
    }

    public function update(Document\DocumentInterface $document): bool
    {
        if (null === $sale = $document->getSale()) {
            throw new LogicException('Invoice\'s sale must be set at this point.');
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
        $data = $this->buildInvoiceAddress($document);
        if ($document->getInvoiceAddress() !== $data) {
            $document->setInvoiceAddress($data);
            $changed = true;
        }

        // Delivery address
        $data = $this->buildDeliveryAddress($document);
        if ($document->getDeliveryAddress() !== $data) {
            $document->setDeliveryAddress($data);
            $changed = true;
        }

        // RelayPoint
        $data = $this->buildRelayPointAddress($document);
        if ($document->getRelayPoint() !== $data) {
            $document->setRelayPoint($data);
            $changed = true;
        }

        // Comment
        if ($document->getComment() !== $comment = $sale->getDocumentComment()) {
            $document->setComment($comment);
            $changed = true;
        }

        return $changed;
    }

    protected function buildInvoiceAddress(Document\DocumentInterface $document): array
    {
        return $this->buildAddressData($document->getSale()->getInvoiceAddress());
    }

    protected function buildDeliveryAddress(Document\DocumentInterface $document): ?array
    {
        if (null === $address = $document->getSale()->getDeliveryAddress()) {
            return null;
        }

        return $this->buildAddressData($address);
    }

    protected function buildRelayPointAddress(Document\DocumentInterface $document): ?array
    {
        if (null === $address = $document->getSale()->getRelayPoint()) {
            return null;
        }

        return $this->buildAddressData($address);
    }

    public function buildGoodLine(
        Common\SaleItemInterface   $item,
        Document\DocumentInterface $document
    ): ?Document\DocumentLineInterface {
        // Abort if document contains one of the public parents
        if ($item->isPrivate() && DocumentUtil::hasPublicParent($document, $item)) {
            return null;
        }

        $line = null;

        // Skip compound with only public children
        if (!($item->isCompound() && !$item->hasPrivateChildren())) {
            // Create line if not found
            if (null === $line = DocumentUtil::findGoodLine($document, $item)) {
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

    public function buildDiscountLine(
        Common\SaleAdjustmentInterface $adjustment,
        Document\DocumentInterface     $document
    ): ?Document\DocumentLineInterface {
        if ($adjustment->getType() !== Common\AdjustmentTypes::TYPE_DISCOUNT) {
            throw new InvalidArgumentException('Unexpected adjustment type.');
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

    public function buildShipmentLine(Document\DocumentInterface $document): ?Document\DocumentLineInterface
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
     */
    protected function buildCustomerData(Common\SaleInterface $sale): array
    {
        if ($customer = $sale->getCustomer()) {
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
     */
    protected function buildAddressData(Common\AddressInterface $address): array
    {
        return $this->addressTransformer->transformAddress($address, [
            'digicode1',
            'digicode2',
            'intercom',
            'information',
            'latitude',
            'longitude',
        ]);
    }

    /**
     * Formats the given phone number.
     */
    protected function formatPhoneNumber(?PhoneNumber $number): ?string
    {
        if ($number) {
            return $this->phoneNumberUtil->format($number, PhoneNumberFormat::INTERNATIONAL);
        }

        return null;
    }

    /**
     * Builds the document's goods lines.
     */
    protected function buildGoodsLines(Document\DocumentInterface $document): void
    {
        foreach ($document->getSale()->getItems() as $item) {
            $this->buildGoodLine($item, $document);
        }
    }

    /**
     * Builds the document's discounts lines.
     */
    protected function buildDiscountsLines(Document\DocumentInterface $document): void
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
     */
    protected function createLine(
        Document\DocumentInterface $document
    ): Document\DocumentLineInterface {
        return new Document\DocumentLine();
    }
}
