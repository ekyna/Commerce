<?php

namespace Ekyna\Component\Commerce\Stock\Model;

use Ekyna\Component\Commerce\Exception\UnexpectedValueException;

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
     * @return string[]
     */
    static public function getModes(): array
    {
        return [
            self::MODE_DISABLED,
            self::MODE_MANUAL,
            self::MODE_AUTO,
            self::MODE_JUST_IN_TIME,
        ];
    }

    /**
     * Returns whether or not the given mode is valid.
     *
     * @param string $mode
     * @param bool $throwException
     *
     * @return bool
     */
    static public function isValidMode(string $mode, bool $throwException = false): bool
    {
        if (in_array($mode, self::getModes(), true)) {
            return true;
        }

        if ($throwException) {
            throw new UnexpectedValueException("Unknown mode '$mode'.");
        }

        return false;
    }

    /**
     * Returns whether the mode A has a better availability than the mode B.
     *
     * @param string $modeA
     * @param string $modeB
     *
     * @return bool
     */
    static public function isBetterMode(string $modeA, string $modeB): bool
    {
        self::isValidMode($modeA, true);
        self::isValidMode($modeB, true);

        if ($modeA === self::MODE_DISABLED) {
            return $modeB !== self::MODE_DISABLED;
        } elseif ($modeA === self::MODE_JUST_IN_TIME) {
            return in_array($modeB, [self::MODE_MANUAL, self::MODE_AUTO], true);
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
