<?php

namespace Ekyna\Component\Commerce\Document\Model;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;

/**
 * Class DocumentTypes
 * @package Ekyna\Component\Commerce\Document\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class DocumentTypes
{
    // Sale
    public const TYPE_FORM           = 'form';
    public const TYPE_VOUCHER        = 'voucher';
    public const TYPE_QUOTE          = 'quote';
    public const TYPE_PROFORMA       = 'proforma';
    public const TYPE_CONFIRMATION   = 'confirmation';

    // Invoice
    public const TYPE_INVOICE        = 'invoice';
    public const TYPE_CREDIT         = 'credit';

    // Shipment
    public const TYPE_SHIPMENT_FORM  = 'shipment_form';
    public const TYPE_SHIPMENT_BILL  = 'shipment_bill';

    // Supplier order
    public const TYPE_SUPPLIER_ORDER = 'supplier_order';


    /**
     * Returns all the document types.
     *
     * @return array
     */
    static public function getTypes(): array
    {
        return [
            self::TYPE_FORM,
            self::TYPE_VOUCHER,
            self::TYPE_QUOTE,
            self::TYPE_PROFORMA,
            self::TYPE_CONFIRMATION,
            self::TYPE_INVOICE,
            self::TYPE_CREDIT,
            self::TYPE_SHIPMENT_FORM,
            self::TYPE_SHIPMENT_BILL,
        ];
    }

    /**
     * Returns the sale document types.
     *
     * @return array
     */
    static public function getSaleTypes(): array
    {
        return [
            self::TYPE_FORM,
            self::TYPE_VOUCHER,
            self::TYPE_QUOTE,
            self::TYPE_PROFORMA,
            self::TYPE_CONFIRMATION,
        ];
    }

    /**
     * Returns whether the given sale type is valid or not.
     *
     * @param string $type
     *
     * @return bool
     */
    static public function isValidSaleType(string $type): bool
    {
        return in_array($type, self::getSaleTypes(), true);
    }

    /**
     * Returns the invoice document types.
     *
     * @return array
     */
    static public function getInvoiceTypes(): array
    {
        return [
            self::TYPE_INVOICE,
            self::TYPE_CREDIT,
        ];
    }

    /**
     * Returns whether the given invoice type is valid or not.
     *
     * @param string $type
     *
     * @return bool
     */
    static public function isValidInvoiceType(string $type): bool
    {
        return in_array($type, self::getInvoiceTypes(), true);
    }

    /**
     * Returns the sale & invoice document types.
     *
     * @return array
     */
    static public function getSaleAndInvoiceTypes(): array
    {
        return array_merge(self::getSaleTypes(), self::getInvoiceTypes());
    }

    /**
     * Returns the invoice document types.
     *
     * @return array
     */
    static public function getShipmentTypes(): array
    {
        return [
            self::TYPE_SHIPMENT_FORM,
            self::TYPE_SHIPMENT_BILL,
        ];
    }

    /**
     * Returns whether the given shipment type is valid or not.
     *
     * @param string $type
     *
     * @return bool
     */
    static public function isValidShipmentType(string $type): bool
    {
        return in_array($type, self::getShipmentTypes(), true);
    }

    /**
     * Returns the sale classes supported by the given type for document generation.
     *
     * @param string $type
     *
     * @return array
     */
    static public function getClasses(string $type): array
    {
        switch ($type) {
            case self::TYPE_FORM:
                return [CartInterface::class];
            case self::TYPE_QUOTE:
                return [QuoteInterface::class];
            case self::TYPE_PROFORMA:
                return [QuoteInterface::class, OrderInterface::class];
            case self::TYPE_CONFIRMATION:
                return [OrderInterface::class];
            case self::TYPE_VOUCHER:
                return [];
            default:
                throw new InvalidArgumentException("Unexpected type '$type'.");
        }
    }

    /**
     * Disabled constructor.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}
