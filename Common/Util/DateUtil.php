<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Util;

use DateTime;
use DateTimeInterface;
use IntlDateFormatter;

use function str_pad;

use const STR_PAD_LEFT;

/**
 * Class DateUtil
 * @package Ekyna\Component\Commerce\Common\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO    Move to Resource component
 */
final class DateUtil
{
    public const DATE_FORMAT     = 'Y-m-d';
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /** @var array<string, array<int, string>> */
    private static array $months = [];

    /**
     * Returns whether the given dates are the same.
     */
    public static function equals(?DateTimeInterface $a, ?DateTimeInterface $b): bool
    {
        if (!$a && !$b) {
            return true;
        }

        if ($a && $b && $a->getTimestamp() === $b->getTimestamp()) {
            return true;
        }

        return false;
    }

    public static function today(): string
    {
        return (new DateTime())->format(self::DATE_FORMAT);
    }

    /**
     * Returns the localized months list.
     *
     * @return array<int, string>
     */
    public static function getMonths(string $locale = 'en'): array
    {
        if (isset(self::$months[$locale])) {
            return self::$months[$locale];
        }

        self::$months[$locale] = [];
        for ($m = 1; $m <= 12; $m++) {
            $month = new DateTime('2000-' . str_pad((string)$m, 2, '0', STR_PAD_LEFT) . '-01');

            self::$months[$locale][$m] = IntlDateFormatter::formatObject($month, 'MMMM', $locale);
        }

        return self::$months[$locale];
    }

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
