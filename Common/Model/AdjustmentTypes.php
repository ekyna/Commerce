<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

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
     * @param bool   $throw
     *
     * @return bool
     */
    static public function isValidType($type, $throw = true)
    {
        if (in_array($type, static::getTypes(), true)) {
            return true;
        }

        if ($throw) {
            throw new InvalidArgumentException('Invalid adjustment type.');
        }

        return false;
    }

    /**
     * Disabled Constructor.
     */
    private function __construct()
    {
    }
}
