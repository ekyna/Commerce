<?php

namespace Ekyna\Component\Commerce\Invoice\Model;

/**
 * Class InvoiceTypes
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceTypes
{
    const TYPE_INVOICE = 'invoice';
    const TYPE_CREDIT  = 'credit';


    /**
     * Returns the invoice types.
     *
     * @return array
     */
    static public function getTypes()
    {
        return [
            static::TYPE_INVOICE,
            static::TYPE_CREDIT,
        ];
    }

    /**
     * Returns whether the given type is valid or not.
     *
     * @param string $type
     *
     * @return bool
     */
    static public function isValidType($type)
    {
        return in_array($type, static::getTypes(), true);
    }

    /**
     * Returns whether the given invoice or type is of type 'invoice'.
     *
     * @param InvoiceInterface|string $invoiceOrType
     *
     * @return bool
     */
    static public function isInvoice($invoiceOrType)
    {
        if ($invoiceOrType instanceof InvoiceInterface) {
            $invoiceOrType = $invoiceOrType->getType();
        }

        return $invoiceOrType === static::TYPE_INVOICE;
    }

    /**
     * Returns whether the given invoice or type is of type 'credit'.
     *
     * @param InvoiceInterface|string $invoiceOrType
     *
     * @return bool
     */
    static public function isCredit($invoiceOrType)
    {
        if ($invoiceOrType instanceof InvoiceInterface) {
            $invoiceOrType = $invoiceOrType->getType();
        }

        return $invoiceOrType === static::TYPE_CREDIT;
    }
}
