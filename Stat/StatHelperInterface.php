<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stat;

use DateTimeInterface;
use Ekyna\Component\Resource\Model\DateRange;

/**
 * Interface StatHelperInterface
 * @package Ekyna\Component\Commerce\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StatHelperInterface
{
    public function getYearForDate(DateTimeInterface $date): string;

    public function getYearRangeForDate(DateTimeInterface $date): DateRange;

    public function getYearRangeForYear(string $year): DateRange;
}
