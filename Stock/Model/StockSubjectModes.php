<?php

namespace Ekyna\Component\Commerce\Stock\Model;

/**
 * Class StockSubjectModes
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class StockSubjectModes
{
    const MODE_DISABLED     = 'disabled';
    const MODE_ENABLED      = 'enabled';
    const MODE_JUST_IN_TIME = 'just_in_time';


    /**
     * Returns all the modes.
     *
     * @return array
     */
    static public function getModes()
    {
        return [
            static::MODE_DISABLED,
            static::MODE_ENABLED,
            static::MODE_JUST_IN_TIME,
        ];
    }

    /**
     * Returns whether or not the given mode is valid.
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
