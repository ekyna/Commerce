<?php

namespace Ekyna\Component\Commerce\Common\Util;

/**
 * Class DateUtil
 * @package Ekyna\Component\Commerce\Common\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class DateUtil
{
    public const DATE_FORMAT     = 'Y-m-d';
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';


    /**
     * Returns whether the given dates are the same.
     *
     * @param \DateTime|null $a
     * @param \DateTime|null $b
     *
     * @return bool
     */
    public static function equals(\DateTime $a = null, \DateTime $b = null): bool
    {
        if (!$a && !$b) {
            return true;
        }

        if ($a && $b && $a->getTimestamp() === $b->getTimestamp()) {
            return true;
        }

        return false;
    }

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
