<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface SaleItemAdjustmentInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleItemAdjustmentInterface extends AdjustmentInterface
{
    public function getItem(): ?SaleItemInterface;

    public function setItem(?SaleItemInterface $item): SaleItemAdjustmentInterface;
}
