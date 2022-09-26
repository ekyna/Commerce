<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\View;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;

/**
 * Interface ViewTypeRegistryInterface
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ViewTypeRegistryInterface
{
    /**
     * Registers the view type.
     */
    public function addType(ViewTypeInterface $type): void;

    /**
     * Returns the view types supporting the given sale.
     *
     * @return array<ViewTypeInterface>
     */
    public function getTypesForSale(SaleInterface $sale): array;
}
