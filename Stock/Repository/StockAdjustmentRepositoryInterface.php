<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Repository;

use DateTime;
use DateTimeInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface StockAdjustmentRepositoryInterface
 * @package Ekyna\Component\Commerce\Stock\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<StockAdjustmentInterface>
 */
interface StockAdjustmentRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the stock adjustment for the given month.
     *
     * @return array<StockAdjustmentInterface>
     */
    public function findByMonth(DateTimeInterface $month): array;
}
