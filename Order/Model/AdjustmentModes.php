<?php

namespace Ekyna\Component\Commerce\Order\Model;

/**
 * Class AdjustmentModes
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class AdjustmentModes
{
    const MODE_FLAT = 'flat';
    const MODE_PERCENT = 'percent';


    /**
     * Returns all the modes.
     *
     * @return array
     */
    static public function getModes()
    {
        return [
            static::MODE_FLAT,
            static::MODE_PERCENT,
        ];
    }

    /**
     * Returns whether the given mode is valid or not.
     *
     * @param string $mode
     *
     * @return bool
     */
    static public function isValidMode($mode)
    {
        return in_array($mode, static::getModes(), true);
    }
}
