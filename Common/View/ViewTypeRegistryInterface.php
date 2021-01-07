<?php

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
     *
     * @param ViewTypeInterface $type
     */
    public function addType(ViewTypeInterface $type): void;

    /**
     * Returns the view types supporting the given sale.
     *
     * @param SaleInterface $sale
     *
     * @return array|ViewTypeInterface[]
     */
    public function getTypesForSale(SaleInterface $sale): array;
}
