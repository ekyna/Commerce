<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Repository;

use DateTime;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface StockAdjustmentRepositoryInterface
 * @package Ekyna\Component\Commerce\Stock\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface StockAdjustmentRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the stock adjustment for the given month.
     *
     * @param DateTime $month
     *
     * @return StockAdjustmentInterface[]
     */
    public function findByMonth(DateTime $month): array;
}
