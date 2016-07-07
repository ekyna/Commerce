<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Order\Model\OrderItemAdjustmentInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;

/**
 * Class OrderItemAdjustment
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItemAdjustment extends AbstractAdjustment implements OrderItemAdjustmentInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var OrderItemInterface
     */
    protected $item;


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

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
    public function setItem(OrderItemInterface $item = null)
    {
        $this->item = $item;
        return $this;
    }
}
