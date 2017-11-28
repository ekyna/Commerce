<?php

namespace Ekyna\Component\Commerce\Document\Builder;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Document\Model as Document;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
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
     * @var PhoneNumberUtil
     */
    private $phoneNumberUtil;


    /**
     * Constructor.
     *
     * @param PhoneNumberUtil $phoneNumberUtil
     */
    public function __construct(PhoneNumberUtil $phoneNumberUtil = null)
    {
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

        $changed = false;

        // Currency
        $code = $sale->getCurrency()->getCode();
        if ($document->getCurrency() !== $code) {
            $document->setCurrency($code);
            $changed = true;
            // TODO Convert prices / Recalculate ?
        }

        // Customer
        $data = $this->buildCustomerData($sale);
        if ($document->getCustomer() !== $data) {
            $document->setCustomer($data);
            $changed = true;
        }

        // Invoice address
        $data = $this->buildAddressData($sale->getInvoiceAddress());
        if ($document->getInvoiceAddress() !== $data) {
            $document->setInvoiceAddress($data);
            $changed = true;
        }

        // Delivery address
        $data = $sale->getDeliveryAddress()
            ? $this->buildAddressData($sale->getDeliveryAddress())
            : null;
        if ($document->getDeliveryAddress() !== $data) {
            $document->setDeliveryAddress($data);
            $changed = true;
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function buildGoodLine(Common\SaleItemInterface $item, Document\DocumentInterface $document, $recurse = true)
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
                    ->setType(Document\DocumentLineTypes::TYPE_GOOD)
                    ->setSaleItem($item)
                    ->setDesignation($item->getDesignation())
                    ->setDescription($item->getDescription())
                    ->setReference($item->getReference())
                    ->setQuantity($item->getTotalQuantity());

                $document->addLine($line);
            }

            $this->postBuildLine($line);
        }

        if ($recurse && $item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $this->buildGoodLine($child, $document);
            }
        }

        return $line;
    }

    /**
     * @inheritdoc
     */
    public function buildDiscountLine(Common\AdjustmentInterface $adjustment, Document\DocumentInterface $document)
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
                ->setType(Document\DocumentLineTypes::TYPE_DISCOUNT)
                ->setSaleAdjustment($adjustment)
                ->setDesignation($adjustment->getDesignation());

            $document->addLine($line);
        }

        $this->postBuildLine($line);

        return $line;
    }

    /**
     * @inheritdoc
     */
    public function buildShipmentLine(Document\DocumentInterface $document)
    {
        $sale = $document->getSale();

        if (0 >= $sale->getShipmentAmount()) {
            return null;
        }

        // Existing line lookup
        $shipmentLines = $document->getLinesByType(Document\DocumentLineTypes::TYPE_SHIPMENT);
        $line = !empty($shipmentLines) ? current($shipmentLines) : null;

        // Not found, create it
        if (null === $line) {
            $line = $this->createLine($document);
            $line
                ->setType(Document\DocumentLineTypes::TYPE_SHIPMENT)
                ->setDesignation($sale->getPreferredShipmentMethod()->getTitle());

            $document->addLine($line);
        }

        $this->postBuildLine($line);

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
     *
     * @return array
     */
    protected function buildAddressData(Common\AddressInterface $address)
    {
        // TODO localize
        $country = Intl::getRegionBundle()->getCountryName($address->getCountry()->getCode());

        $fullName = trim($address->getFirstName() . ' ' . $address->getLastName());

        // TODO if empty full customer name

        return [
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
     * Post build good line.
     *
     * @param Document\DocumentLineInterface $line
     */
    protected function postBuildLine(Document\DocumentLineInterface $line)
    {
    }

    /**
     * Creates a new line.
     *
     * @param Document\DocumentInterface $document
     *
     * @return Document\DocumentLineInterface
     */
    protected function createLine(Document\DocumentInterface $document)
    {
        return new Document\DocumentLine();
    }
}
