<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Ekyna\Component\Commerce\Manufacture\Model\ProductionItemInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;

/**
 * Class ProductionPrioritizer
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductionPrioritizerInterface
{
    public function prioritize(ProductionOrderInterface $order): bool;

    public function prioritizeItem(ProductionItemInterface $item): bool;
}
