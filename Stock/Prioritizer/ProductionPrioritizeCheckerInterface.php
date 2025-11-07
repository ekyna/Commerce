<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;


use Ekyna\Component\Commerce\Manufacture\Model\ProductionItemInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;

/**
 * Class ProductionPrioritizeChecker
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductionPrioritizeCheckerInterface
{
    public function check(ProductionOrderInterface $order): bool;

    public function checkItem(ProductionItemInterface $item): bool;
}
