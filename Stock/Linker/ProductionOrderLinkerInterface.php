<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Linker;

use Ekyna\Component\Commerce\Manufacture\Model\ProductionInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;

/**
 * Class ProductionOrderLinker
 * @package Ekyna\Component\Commerce\Stock\Linker
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductionOrderLinkerInterface
{
    public function linkOrder(ProductionOrderInterface $order): void;

    public function applyOrder(ProductionOrderInterface $order): void;

    public function unlinkOrder(ProductionOrderInterface $order): void;

    public function updateData(ProductionOrderInterface $order): void;

    public function linkProduction(ProductionInterface $production): void;

    public function applyProduction(ProductionInterface $production): void;

    public function unlinkProduction(ProductionInterface $production): void;
}
