<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

/**
 * Class OpeningHour
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OpeningHour
{
    /**
     * @var int
     */
    private $day;

    /**
     * @var array
     */
    private $ranges = [];


    /**
     * Returns the day.
     *
     * @return int
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Sets the day.
     *
     * @param int $day (ISO-8601 day of week number, 1 for monday, 7 for sunday)
     *
     * @return OpeningHour
     */
    public function setDay(int $day)
    {
        $this->day = $day;

        return $this;
    }

    /**
     * Returns the ranges.
     *
     * @return array
     */
    public function getRanges()
    {
        return $this->ranges;
    }

    /**
     * Sets the ranges.
     *
     * @param string $from
     * @param string $to
     *
     * @return OpeningHour
     */
    public function addRanges(string $from, string $to)
    {
        $this->ranges[] = [
            'from' => $from,
            'to'   => $to,
        ];

        return $this;
    }
}
