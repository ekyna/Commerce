<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface SaleAdjustmentInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleAdjustmentInterface extends AdjustmentInterface
{
    /**
     * Returns the sale.
     *
     * @return SaleInterface
     */
    public function getSale(): ?SaleInterface;
}
