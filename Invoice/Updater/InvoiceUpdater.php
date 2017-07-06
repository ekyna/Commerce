<?php

namespace Ekyna\Component\Commerce\Invoice\Updater;

use Ekyna\Component\Commerce\Common\Calculator\AmountsCalculatorInterface;
use Ekyna\Component\Commerce\Common\Calculator\Result;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Invoice\Model;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Intl\Intl;

/**
 * Class InvoiceUpdater
 * @package Ekyna\Component\Commerce\Invoice\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceUpdater implements InvoiceUpdaterInterface
{
    /**
     * @var AmountsCalculatorInterface
     */
    private $amountsCalculator;

    /**
     * @var PhoneNumberUtil
     */
    private $phoneNumberUtil;


    /**
     * Constructor.
     *
     * @param AmountsCalculatorInterface $amountsCalculator
     * @param PhoneNumberUtil            $phoneNumberUtil
     */
    public function __construct(AmountsCalculatorInterface $amountsCalculator, PhoneNumberUtil $phoneNumberUtil = null)
    {
        $this->amountsCalculator = $amountsCalculator;
        $this->phoneNumberUtil = $phoneNumberUtil ?: PhoneNumberUtil::getInstance();
    }

    /**
     * @inheritdoc
     */
    public function updatePricing(Model\InvoiceInterface $invoice)
    {
        if (null === $sale = $invoice->getSale()) {
            throw new LogicException("Invoice's sale must be set at this point.");
        }

        $changed = false;

        $result = new Result();

        // Goods lines
        foreach ($invoice->getLinesByType(Model\InvoiceLineTypes::TYPE_GOOD) as $line) {
            $changed |= $this->updateGoodLine($line);

            $result->merge($this->buildResultFromLine($line));
        }

        // Discount lines
        $goodsResult = clone $result;
        foreach ($invoice->getLinesByType(Model\InvoiceLineTypes::TYPE_DISCOUNT) as $line) {
            $changed |= $this->updateDiscountLine($line, $goodsResult);

            $result->merge($this->buildResultFromLine($line));
        }

        // Invoice goods base (after discounts)
        if ($invoice->getGoodsBase() !== $result->getBase()) {
            $invoice->setGoodsBase($result->getBase());
            $changed = true;
        }

        // Shipment lines
        $shipmentBase = 0;
        foreach ($invoice->getLinesByType(Model\InvoiceLineTypes::TYPE_SHIPMENT) as $line) {
            $changed |= $this->updateShipmentLine($line);

            $shipmentResult = $this->buildResultFromLine($line);
            $shipmentBase += $shipmentResult->getBase();

            $result->merge($shipmentResult);
        }

        // Invoice shipment base.
        if ($invoice->getShipmentBase() !== $shipmentBase) {
            $invoice->setShipmentBase($shipmentBase);
            $changed = true;
        }

        // Invoice taxes total
        $taxesTotal = $result->getTaxTotal();
        if ($invoice->getTaxesTotal() !== $taxesTotal) {
            $invoice->setTaxesTotal($taxesTotal);
            $changed = true;
        }

        // Invoice grand total
        $grandTotal = $result->getTotal();
        if ($invoice->getGrandTotal() !== $grandTotal) {
            $invoice->setGrandTotal($grandTotal);
            $changed = true;
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function updateData(Model\InvoiceInterface $invoice)
    {
        if (null === $sale = $invoice->getSale()) {
            throw new LogicException("Invoice's sale must be set at this point.");
        }

        $changed = false;

        // Currency
        $code = $sale->getCurrency()->getCode();
        if ($invoice->getCurrency() !== $code) {
            $invoice->setCurrency($code);
            $changed = true;
        }

        // Customer
        $data = $this->buildCustomer($sale);
        if ($invoice->getCustomer() !== $data) {
            $invoice->setCustomer($data);
            $changed = true;
        }

        // Invoice address
        $data = $this->buildAddress($sale->getInvoiceAddress());
        if ($invoice->getInvoiceAddress() !== $data) {
            $invoice->setInvoiceAddress($data);
            $changed = true;
        }

        // Delivery address
        $data = $sale->getDeliveryAddress()
            ? $this->buildAddress($sale->getDeliveryAddress())
            : null;
        if ($invoice->getDeliveryAddress() !== $data) {
            $invoice->setDeliveryAddress($data);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Builds the invoice's customer data.
     *
     * @param SaleInterface $sale
     *
     * @return array
     */
    protected function buildCustomer(SaleInterface $sale)
    {
        if (null !== $customer = $sale->getCustomer()) {
            return [
                'number'    => $customer->getNumber(),
                'company'   => $customer->getCompany(),
                'full_name' => $customer->getFirstName() . ' ' . $customer->getLastName(),
                'email'     => $customer->getEmail(),
                'phone'     => $this->formatPhoneNumber($customer->getPhone()),
                'mobile'    => $this->formatPhoneNumber($customer->getMobile()),
            ];
        } else {
            return [
                'number'    => null,
                'company'   => $sale->getCompany(),
                'full_name' => $sale->getFirstName() . ' ' . $sale->getLastName(),
                'email'     => $sale->getEmail(),
                'phone'     => null,
                'mobile'    => null,
            ];
        }
    }

    /**
     * Builds the invoice's address data.
     *
     * @param AddressInterface $address
     *
     * @return array
     */
    protected function buildAddress(AddressInterface $address)
    {
        // TODO localize
        $country = Intl::getRegionBundle()->getCountryName($address->getCountry()->getCode());

        return [
            'company'     => $address->getCompany(),
            'full_name'   => $address->getFirstName() . ' ' . $address->getLastName(),
            'street'      => $address->getStreet(),
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
     * Builds a result form the given line.
     *
     * @param Model\InvoiceLineInterface $line
     *
     * @return Result
     */
    protected function buildResultFromLine(Model\InvoiceLineInterface $line)
    {
        $result = new Result();

        $result->addBase($line->getNetPrice());

        foreach ($line->getTaxesDetails() as $detail) {
            $result->addTax($detail['name'], $detail['rate'], $detail['amount']);
        }

        $result->multiply($line->getQuantity());

        return $result;
    }

    /**
     * Updates the given 'good' line.
     *
     * @param Model\InvoiceLineInterface $line
     *
     * @return bool
     * @throws LogicException
     */
    protected function updateGoodLine(Model\InvoiceLineInterface $line)
    {
        if ($line->getType() !== Model\InvoiceLineTypes::TYPE_GOOD) {
            throw new LogicException(sprintf(
                "Expected invoice line with type '%s'.",
                Model\InvoiceLineTypes::TYPE_GOOD
            ));
        }

        $changed = false;

        if (null === $item = $line->getSaleItem()) {
            throw new LogicException("Invoice can't be recalculated.");
        }

        $result = $this->amountsCalculator->calculateSaleItem($item);

        $netUnit = $result->getBase();
        if ($line->getNetPrice() != $netUnit) {
            $line->setNetPrice($netUnit);
            $changed = true;
        }

        $taxesDetails = $this->buildTaxesDetails($result);
        if ($line->getTaxesDetails() !== $taxesDetails) {
            $line->setTaxesDetails($taxesDetails);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Updates the given 'discount' line.
     *
     * @param Model\InvoiceLineInterface $line
     * @param Result                     $goodsResult
     *
     * @return bool
     * @throws LogicException
     */
    protected function updateDiscountLine(Model\InvoiceLineInterface $line, Result $goodsResult)
    {
        if ($line->getType() !== Model\InvoiceLineTypes::TYPE_DISCOUNT) {
            throw new LogicException(sprintf(
                "Expected invoice line with type '%s'.",
                Model\InvoiceLineTypes::TYPE_DISCOUNT
            ));
        }

        $changed = false;

        if (null === $adjustment = $line->getSaleAdjustment()) {
            throw new LogicException("Invoice can't be recalculated.");
        }

        $result = $this->amountsCalculator->calculateDiscountAdjustment($adjustment, $goodsResult);

        $netUnit = $result->getBase();
        if ($line->getNetPrice() != $netUnit) {
            $line->setNetPrice($netUnit);
            $changed = true;
        }

        $taxesDetails = $this->buildTaxesDetails($result);
        if ($line->getTaxesDetails() !== $taxesDetails) {
            $line->setTaxesDetails($taxesDetails);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Updates the given 'shipment' line.
     *
     * @param Model\InvoiceLineInterface $line
     *
     * @return bool
     * @throws LogicException
     */
    protected function updateShipmentLine(Model\InvoiceLineInterface $line)
    {
        if ($line->getType() !== Model\InvoiceLineTypes::TYPE_SHIPMENT) {
            throw new LogicException(sprintf(
                "Expected invoice line with type '%s'.",
                Model\InvoiceLineTypes::TYPE_SHIPMENT
            ));
        }

        $changed = false;

        $sale = $line->getInvoice()->getSale();

        $result = $this->amountsCalculator->calculateShipment($sale);

        $netUnit = $result->getBase();
        if ($line->getNetPrice() != $netUnit) {
            $line->setNetPrice($netUnit);
            $changed = true;
        }

        $taxesDetails = $this->buildTaxesDetails($result);
        if ($line->getTaxesDetails() !== $taxesDetails) {
            $line->setTaxesDetails($taxesDetails);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Builds the taxes details array from the given result.
     *
     * @param Result $result
     *
     * @return array
     */
    protected function buildTaxesDetails(Result $result)
    {
        $taxes = [];

        foreach ($result->getTaxes() as $tax) {
            $taxes[] = [
                'name'   => $tax->getName(),
                'rate'   => $tax->getRate(),
                'amount' => $tax->getAmount(),
            ];
        }

        return $taxes;
    }
}
