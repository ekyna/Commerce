<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class AdjustmentModes
 * @package Ekyna\Component\Commerce\Common\Model
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
     * @param bool $throw
     *
     * @return bool
     */
    static public function isValidMode($mode, $throw = true)
    {
        if (in_array($mode, static::getModes(), true)) {
            return true;
        }

        if ($throw) {
            throw new InvalidArgumentException('Invalid adjustment mode.');
        }

        return false;
    }
}
