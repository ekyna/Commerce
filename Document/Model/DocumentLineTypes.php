<?php

namespace Ekyna\Component\Commerce\Document\Model;

/**
 * Class DocumentLineTypes
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentLineTypes
{
    const TYPE_GOOD     = 'good';
    const TYPE_DISCOUNT = 'discount';
    const TYPE_SHIPMENT = 'shipment';


    /**
     * Returns the invoice line types.
     *
     * @return array
     */
    static public function getTypes()
    {
        return [
            static::TYPE_GOOD,
            static::TYPE_DISCOUNT,
            static::TYPE_SHIPMENT,
        ];
    }

    /**
     * Returns whether the given line type is valid or not.
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
