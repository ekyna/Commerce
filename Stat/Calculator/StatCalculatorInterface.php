<?php

namespace Ekyna\Component\Commerce\Stat\Calculator;

/**
 * Interface StatCalculatorInterface
 * @package Ekyna\Component\Commerce\Stat\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StatCalculatorInterface
{
    /**
     * Calculates the in stock statistics.
     *
     * @return array
     */
    public function calculateStockStats();

    /**
     * Calculates the in order stats for the given day.
     *
     * @param \DateTime $date
     *
     * @return array
     */
    public function calculateDayOrderStats(\DateTime $date);

    /**
     * Calculates the in order stats for the given month.
     *
     * @param \DateTime $date
     *
     * @return array
     */
    public function calculateMonthOrderStats(\DateTime $date);

    /**
     * Calculates the in order stats for the given year.
     *
     * @param \DateTime $date
     *
     * @return array
     */
    public function calculateYearOrderStats(\DateTime $date);
}