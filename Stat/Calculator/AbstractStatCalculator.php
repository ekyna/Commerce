<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stat\Calculator;

use DateTimeInterface;
use Ekyna\Component\Commerce\Stat\StatHelperInterface;
use Ekyna\Component\Resource\Model\DateRange;

/**
 * Class AbstractStatCalculator
 * @package Ekyna\Component\Commerce\Stat\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractStatCalculator implements StatCalculatorInterface
{
    public function __construct(
        protected readonly StatHelperInterface $statHelper,
    ) {
    }

    public function calculateDayStats(DateTimeInterface $date, StatFilter $filter = null): array
    {
        $start = clone $date;
        $start->setTime(0, 0);

        $end = clone $date;
        $end->setTime(23, 59, 59, 999999);

        return $this->calculateStats(new DateRange($start, $end));
    }

    public function calculateMonthStats(DateTimeInterface $date): array
    {
        $start = clone $date;
        $start->modify('first day of this month');
        $start->setTime(0, 0);

        $end = clone $date;
        $end->modify('last day of this month');
        $end->setTime(23, 59, 59, 999999);

        return $this->calculateStats(new DateRange($start, $end));
    }

    public function calculateYearStats(string $year): array
    {
        $range = $this->statHelper->getYearRangeForYear($year);

        return $this->calculateStats($range);
    }

    abstract protected function calculateStats(DateRange $range): array;
}
