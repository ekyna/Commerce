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
     * Sets whether to skip order containing filtered subject.
     *
     * If skip mode is true :
     *   - If filter excludes subjects : ignore orders containing items assigned to these subjects.
     *   - If filter does NOT exclude subjects : ignore orders containing items NOT assigned to these subjects.
     *
     * If skip mode is false :
     *   - If filter excludes subjects : calculate all orders by ignoring items assigned to these subjects.
     *   - If filter does NOT exclude subjects : calculate all orders by ignoring items NOT assigned to these subjects.
     *
     * @param bool $skip
     */
    public function setSkipMode(bool $skip): void;

    /**
     * Calculates the in stock statistics.
     */
    public function calculateStockStats(): array;

    /**
     * Calculates the in order stats for the given day.
     */
    public function calculateDayOrderStats(DateTimeInterface $date, StatFilter $filter = null): array;

    /**
     * Calculates the in order stats for the given month.
     */
    public function calculateMonthOrderStats(DateTimeInterface $date, StatFilter $filter = null): array;

    /**
     * Calculates the in order stats for the given year.
     */
    public function calculateYearOrderStats(DateTimeInterface $date, StatFilter $filter = null): array;

    /**
     * Creates an empty result.
     */
    public function createEmptyResult(): array;
}
