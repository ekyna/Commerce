<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stat\Calculator;

use DateTimeInterface;

/**
 * Interface StatCalculatorInterface
 * @package Ekyna\Component\Commerce\Stat\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StatCalculatorInterface
{
    /**
     * Calculates the stats for the given day.
     */
    public function calculateDayStats(DateTimeInterface $date): array;

    /**
     * Calculates the stats for the given month.
     */
    public function calculateMonthStats(DateTimeInterface $date): array;

    /**
     * Calculates the stats for the given year.
     */
    public function calculateYearStats(string $year): array;

    /**
     * Creates an empty result.
     */
    public function createEmptyResult(): array;
}
