<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stat;

use DateTime;
use DateTimeInterface;
use Ekyna\Component\Resource\Model\DateRange;

/**
 * Class StatHelper
 * @package Ekyna\Component\Commerce\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatHelper implements StatHelperInterface
{
    public function getYearForDate(DateTimeInterface $date): string
    {
        return $date->format('Y');
    }

    public function getYearRangeForDate(DateTimeInterface $date): DateRange
    {
        return $this->getYearRangeForYear($date->format('Y'));
    }

    public function getYearRangeForYear(string $year): DateRange
    {
        return new DateRange(
            new DateTime('first day of january ' . $year),
            new DateTime('last day of december ' . $year)
        );
    }
}
