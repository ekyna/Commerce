<?php

namespace Ekyna\Component\Commerce\Stock\Helper;

use Ekyna\Component\Commerce\Stock\Model\Availability;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;

/**
 * Interface AvailabilityHelperInterface
 * @package Ekyna\Component\Commerce\Stock\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AvailabilityHelperInterface
{
    /**
     * Returns the subject availability.
     *
     * @param StockSubjectInterface $subject
     * @param bool                  $root
     * @param bool                  $short
     *
     * @return Availability
     */
    public function getAvailability(
        StockSubjectInterface $subject,
        bool $root = true,
        bool $short = false
    ): Availability;

    /**
     * Returns the subject's availability message.
     *
     * @param StockSubjectInterface $subject
     * @param float|null            $quantity
     * @param bool                  $root
     * @param bool                  $short
     *
     * @return string
     */
    public function getAvailabilityMessage(
        StockSubjectInterface $subject,
        float $quantity = null,
        bool $root = true,
        bool $short = false
    ): string;
}
