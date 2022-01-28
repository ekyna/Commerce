<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Accounting\Export;

use DateInterval;
use DatePeriod;
use DateTime;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Exception;

use function is_null;
use function iterator_to_array;

/**
 * Class ExportUtil
 * @package Ekyna\Component\Commerce\Accounting\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class ExportUtil
{
    /**
     * @return array<DateTime>
     */
    public static function buildMonthList(string $year, ?string $month): array
    {
        if (is_null($month)) {
            try {
                $start = new DateTime("$year-01-01");
            } catch (Exception $e) {
                throw new InvalidArgumentException('Failed to create date.');
            }

            return iterator_to_array(new DatePeriod(
                $start,
                new DateInterval('P1M'),
                (clone $start)->modify('last day of december')
            ));
        }

        try {
            return [new DateTime("$year-$month-01")];
        } catch (Exception $e) {
            throw new InvalidArgumentException('Failed to create date.');
        }
    }
}
