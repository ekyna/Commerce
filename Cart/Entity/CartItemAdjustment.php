<?php

namespace Ekyna\Component\Commerce\Cart\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAdjustment;
use Ekyna\Component\Commerce\Cart\Model\CartItemAdjustmentInterface;
use Ekyna\Component\Commerce\Cart\Model\CartItemInterface;

/**
 * Class CartItemAdjustment
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartItemAdjustment extends AbstractAdjustment implements CartItemAdjustmentInterface
{
    /**
     * @var CartItemInterface
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
    public function setItem(CartItemInterface $item = null)
    {
        if ($item != $this->item) {
            $previous = $this->item;
            $this->item = $item;

            if ($previous) {
                $previous->removeAdjustment($this);
            }

            if ($this->item) {
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
}
