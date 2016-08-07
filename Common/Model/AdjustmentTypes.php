<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Class AdjustmentTypes
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class AdjustmentTypes
{
    const TYPE_TAXATION = 'taxation';
    const TYPE_INCLUDED = 'included';
    const TYPE_DISCOUNT = 'discount';


    /**
     * Returns all the types.
     *
     * @return array
     */
    static public function getTypes()
    {
        return [
            static::TYPE_TAXATION,
            static::TYPE_INCLUDED,
            static::TYPE_DISCOUNT,
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
