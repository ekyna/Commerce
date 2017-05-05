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
}
