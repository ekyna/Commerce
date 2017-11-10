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
    const MODE_MANUAL       = 'manual';
    const MODE_AUTO         = 'auto';
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
            static::MODE_MANUAL,
            static::MODE_AUTO,
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

    /**
     * Returns whether the mode A is better than the mode B.
     *
     * @param string $modeA
     * @param string $modeB
     *
     * @return bool
     */
    static public function isBetterMode($modeA, $modeB)
    {
        // TODO Find something more explicit than 'better' (availability ?)

        // TODO assert valid states ?

        if ($modeA === static::MODE_DISABLED) {
            return $modeB !== static::MODE_DISABLED;
        } elseif ($modeA === static::MODE_JUST_IN_TIME) {
            return in_array($modeB, [static::MODE_MANUAL, static::MODE_AUTO], true);
        }

        return false;
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
