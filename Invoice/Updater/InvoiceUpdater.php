<?php

namespace Ekyna\Component\Commerce\Invoice\Updater;

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
     * @inheritdoc
     */
    public function update(Model\InvoiceInterface $invoice)
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
}
