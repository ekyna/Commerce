<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSaleAdjustment;
use Ekyna\Component\Commerce\Order\Model\OrderAdjustmentInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;

/**
 * Class OrderAdjustment
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderAdjustment extends AbstractSaleAdjustment implements OrderAdjustmentInterface
{
    /**
     * @var OrderInterface
     */
    protected $order;


    /**
     * @inheritDoc
     */
    public function getSale()
    {
        return $this->getOrder();
    }

    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @inheritdoc
     */
    public function setOrder(OrderInterface $order = null)
    {
        if ($order !== $this->order) {
            $previous = $this->order;
            $this->order = $order;

            if ($previous) {
                $previous->removeAdjustment($this);
            }

            if ($this->order) {
                $this->order->addAdjustment($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAdjustable()
    {
        return $this->order;
    }
}
