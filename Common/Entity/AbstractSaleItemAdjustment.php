<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model\AdjustableInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemAdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Class AbstractSaleItemAdjustment
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleItemAdjustment extends AbstractAdjustment implements SaleItemAdjustmentInterface
{
    protected ?SaleItemInterface $item = null;


    public function getItem(): ?SaleItemInterface
    {
        return $this->item;
    }

    public function setItem(?SaleItemInterface $item): SaleItemAdjustmentInterface
    {
        $item && $this->assertSaleItemClass($item);

        if ($item === $this->item) {
            return $this;
        }

        if ($previous = $this->item) {
            $this->item = null;
            $previous->removeAdjustment($this);
        }

        if ($this->item = $item) {
            $this->item->addAdjustment($this);
        }

        return $this;
    }

    public function getAdjustable(): ?AdjustableInterface
    {
        return $this->item;
    }

    /**
     * Asserts that the given sale item is an instance of the expected class.
     */
    abstract protected function assertSaleItemClass(SaleItemInterface $item): void;
}
