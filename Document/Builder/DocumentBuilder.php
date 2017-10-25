<?php

namespace Ekyna\Component\Commerce\Document\Builder;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Document\Model;
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
     * @param PhoneNumberUtil            $phoneNumberUtil
     */
    public function __construct(PhoneNumberUtil $phoneNumberUtil = null)
    {
        $this->phoneNumberUtil = $phoneNumberUtil ?: PhoneNumberUtil::getInstance();
    }

    /**
     * @inheritDoc
     */
    public function build(Model\DocumentInterface $document)
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
    public function update(Model\DocumentInterface $document)
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
     * Builds the document's customer data.
     *
     * @param Common\SaleInterface $sale
     *
     * @return array
     */
    public function buildCustomerData(Common\SaleInterface $sale)
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
    public function buildAddressData(Common\AddressInterface $address)
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
     * @param Model\DocumentInterface $document
     */
    protected function buildGoodsLines(Model\DocumentInterface $document)
    {
        foreach ($document->getSale()->getItems() as $item) {
            $this->buildGoodLine($document, $item);
        }
    }

    /**
     * Builds the document good line from the given sale item.
     *
     * @param Model\DocumentInterface $document
     * @param Common\SaleItemInterface $item
     */
    protected function buildGoodLine(Model\DocumentInterface $document, Common\SaleItemInterface $item)
    {
        $description = null;
        if ($item->isCompound() && $item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $this->buildGoodLine($document, $child);
            }

            return;
        }

        $line = $this->createLine($document);
        $line
            ->setType(Model\DocumentLineTypes::TYPE_GOOD)
            ->setSaleItem($item)
            ->setDesignation($item->getDesignation())
            ->setDescription($description)
            ->setReference($item->getReference())
            ->setQuantity($item->getTotalQuantity());

        $document->addLine($line);

        $this->postBuildLine($line);

        if (!$item->isCompound() && $item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $this->buildGoodLine($document, $child);
            }
        }
    }

    /**
     * Builds the document's discounts lines.
     *
     * @param Model\DocumentInterface $document
     */
    protected function buildDiscountsLines(Model\DocumentInterface $document)
    {
        $sale = $document->getSale();

        if (!$sale->hasAdjustments(Common\AdjustmentTypes::TYPE_DISCOUNT)) {
            return;
        }

        $adjustments = $sale->getAdjustments();
        foreach ($adjustments as $adjustment) {
            if ($adjustment->getType() === Common\AdjustmentTypes::TYPE_DISCOUNT) {
                $this->buildDiscountLine($document, $adjustment);
            }
        }
    }

    /**
     * Builds the discount line from the given adjustment.
     *
     * @param Model\DocumentInterface $document
     * @param Common\AdjustmentInterface $adjustment
     */
    protected function buildDiscountLine(Model\DocumentInterface $document, Common\AdjustmentInterface $adjustment)
    {
        if ($adjustment->getType() !== Common\AdjustmentTypes::TYPE_DISCOUNT) {
            throw new InvalidArgumentException("Unexpected adjustment type.");
        }

        $line = $this->createLine($document);
        $line
            ->setType(Model\DocumentLineTypes::TYPE_DISCOUNT)
            ->setSaleAdjustment($adjustment)
            ->setDesignation($adjustment->getDesignation());

        $document->addLine($line);

        $this->postBuildLine($line);
    }

    /**
     * Builds the document's shipment line.
     *
     * @param Model\DocumentInterface $document
     */
    protected function buildShipmentLine(Model\DocumentInterface $document)
    {
        $sale = $document->getSale();

        if (0 >= $sale->getShipmentAmount()) {
            return;
        }

        $line = $this->createLine($document);
        $line
            ->setType(Model\DocumentLineTypes::TYPE_SHIPMENT)
            ->setDesignation($sale->getPreferredShipmentMethod()->getTitle());

        $document->addLine($line);

        $this->postBuildLine($line);
    }

    /**
     * Post build good line.
     *
     * @param Model\DocumentLineInterface $line
     */
    protected function postBuildLine(Model\DocumentLineInterface $line)
    {
    }

    /**
     * Creates a new line.
     *
     * @param Model\DocumentInterface $document
     *
     * @return Model\DocumentLineInterface
     */
    protected function createLine(Model\DocumentInterface $document)
    {
        return new Model\DocumentLine();
    }
}
