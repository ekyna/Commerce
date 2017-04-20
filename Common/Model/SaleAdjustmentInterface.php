<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface SaleAdjustmentInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleAdjustmentInterface extends AdjustmentInterface
{
    public function getSale(): ?SaleInterface;
}
