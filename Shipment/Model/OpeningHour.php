<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

/**
 * Class OpeningHour
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OpeningHour
{
    private ?int $day = null;
    private array $ranges = [];


    public function getDay(): ?int
    {
        return $this->day;
    }

    /**
     * @param int $day (ISO-8601 day of week number, 1 for monday, 7 for sunday)
     */
    public function setDay(int $day): OpeningHour
    {
        $this->day = $day;

        return $this;
    }

    public function getRanges(): array
    {
        return $this->ranges;
    }

    public function addRanges(string $from, string $to): OpeningHour
    {
        $this->ranges[] = [
            'from' => $from,
            'to'   => $to,
        ];

        return $this;
    }
}
