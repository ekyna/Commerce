<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model\SaleItemAdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Class AbstractSaleItemAdjustment
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleItemAdjustment extends AbstractAdjustment implements SaleItemAdjustmentInterface
{
    /**
     * @var SaleItemInterface
     */
    protected $item;


    /**
     * @inheritdoc
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @inheritdoc
     */
    public function setItem(SaleItemInterface $item = null)
    {
        $item && $this->assertSaleItemClass($item);

        if ($item !== $this->item) {
            if ($previous = $this->item) {
                $this->item = null;
                $previous->removeAdjustment($this);
            }

            if ($this->item = $item) {
                $this->item->addAdjustment($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAdjustable()
    {
        return $this->item;
    }

    /**
     * Asserts that the given sale item is an instance of the expected class.
     *
     * @param SaleItemInterface $item
     */
    abstract protected function assertSaleItemClass(SaleItemInterface $item);
}
