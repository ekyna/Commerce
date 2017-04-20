<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;

use function in_array;
use function pow;

/**
 * Class Unit
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class Units
{
    public const PIECE = 'piece';

    // Length
    public const METER      = 'meter';
    public const CENTIMETER = 'centimeter';
    public const MILLIMETER = 'millimeter';
    public const INCH       = 'inch';
    public const FOOT       = 'foot';

    // Weight
    public const KILOGRAM = 'kilogram';
    public const GRAM     = 'gram';

    // Volume
    public const CUBIC_METER = 'cubic_meter';
    public const LITER       = 'liter';
    public const MILLILITER  = 'milliliter';

    // Duration
    public const DAY    = 'day';
    public const HOUR   = 'hour';
    public const MINUTE = 'minute';
    public const SECOND = 'second';


    /**
     * Returns all the units.
     *
     * @return array<string>
     */
    public static function getUnits(): array
    {
        return [
            Units::PIECE,
            // Length
            Units::METER,
            Units::CENTIMETER,
            Units::MILLIMETER,
            Units::INCH,
            Units::FOOT,
            // Weight
            Units::KILOGRAM,
            Units::GRAM,
            // Volume
            Units::CUBIC_METER,
            Units::LITER,
            Units::MILLILITER,
            // Duration
            Units::DAY,
            Units::HOUR,
            Units::MINUTE,
            Units::SECOND,
        ];
    }

    /**
     * Returns whether the given unit is valid.
     */
    public static function isValid(string $unit, bool $throwException = false): bool
    {
        if (in_array($unit, Units::getUnits(), true)) {
            return true;
        }

        if ($throwException) {
            throw new UnexpectedValueException("Unknown unit '$unit'.");
        }

        return false;
    }

    /**
     * Returns the symbol for the given unit.
     */
    public static function getSymbol(string $unit): string
    {
        switch ($unit) {
            case Units::PIECE:
                return 'pcs';

            // Length
            case Units::METER:
                return 'm';
            case Units::CENTIMETER:
                return 'cm';
            case Units::MILLIMETER:
                return 'mm';
            case Units::INCH:
                return 'in';
            case Units::FOOT:
                return 'ft';

            // Weight
            case Units::KILOGRAM:
                return 'kg';
            case Units::GRAM:
                return 'g';

            // Volume
            case Units::CUBIC_METER:
                return 'm³';
            case Units::LITER:
                return 'L';
            case Units::MILLILITER:
                return 'mL';

            // Duration
            case Units::DAY:
                return 'days';
            case Units::HOUR:
                return 'hours';
            case Units::MINUTE:
                return 'minutes';
            case Units::SECOND:
                return 's';

            default:
                throw new UnexpectedValueException("Unknown unit '$unit'.");
        }
    }

    /**
     * Returns the rounding precision for the given unit.
     */
    public static function getPrecision(string $unit): int
    {
        switch ($unit) {
            case Units::PIECE:
            case Units::MILLIMETER:
            case Units::GRAM:
            case Units::MILLILITER:
            case Units::DAY:
            case Units::MINUTE:
            case Units::SECOND:
                return 0;
            case Units::CENTIMETER:
                return 1;
            case Units::INCH:
            case Units::FOOT:
            case Units::HOUR:
                return 2;
            case Units::METER:
            case Units::KILOGRAM:
            case Units::CUBIC_METER:
            case Units::LITER:
                return 3;
            default:
                throw new UnexpectedValueException("Unknown unit '$unit'.");
        }
    }

    /**
     * Rounds the given value for the given unit.
     */
    public static function round(Decimal $value, string $unit = Units::PIECE): Decimal
    {
        if (0 < $precision = Units::getPrecision($unit)) {
            $divider = pow(10, $precision);

            return $value->mul($divider)->floor()->div($divider)->round($precision);
        }

        return $value->floor();
    }

    public static function fixed(Decimal $amount, string $unit = Units::PIECE): string
    {
        return $amount->toFixed(self::getPrecision($unit));
    }

    /**
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}
