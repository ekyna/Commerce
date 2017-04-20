<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Helper;

use Decimal\Decimal;
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
     */
    public function getAvailability(
        StockSubjectInterface $subject,
        bool $root = true,
        bool $short = false
    ): Availability;

    /**
     * Returns the subject's availability message.
     */
    public function getAvailabilityMessage(
        StockSubjectInterface $subject,
        Decimal $quantity = null,
        bool $root = true,
        bool $short = false
    ): string;
}
