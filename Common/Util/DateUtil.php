<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Util;

use DateTime;
use DateTimeInterface;

/**
 * Class DateUtil
 * @package Ekyna\Component\Commerce\Common\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO Move to Resource component
 */
final class DateUtil
{
    public const DATE_FORMAT     = 'Y-m-d';
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';


    /**
     * Returns whether the given dates are the same.
     *
     * @param DateTimeInterface|null $a
     * @param DateTimeInterface|null $b
     *
     * @return bool
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
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
