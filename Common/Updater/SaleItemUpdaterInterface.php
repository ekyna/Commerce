<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Updater;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Class SaleItemUpdater
 * @package Ekyna\Component\Commerce\Common\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleItemUpdaterInterface
{
    public function updateChildrenNetPriceAndDiscount(
        SaleInterface|SaleItemInterface $parent,
        bool                            $persistence = false
    ): bool;

    public function updateNetPriceAndDiscount(SaleItemInterface $item): bool;
}
