<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSaleAdjustment;
use Ekyna\Component\Commerce\Common\Model\AdjustableInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
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
    public function getSale(): ?SaleInterface
    {
        return $this->order;
    }

    /**
     * @inheritdoc
     */
    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    /**
     * @inheritdoc
     */
    public function setOrder(OrderInterface $order = null): OrderAdjustmentInterface
    {
        if ($order !== $this->order) {
            if ($previous = $this->order) {
                $this->order = null;
                $previous->removeAdjustment($this);
            }

            if ($this->order = $order) {
                $this->order->addAdjustment($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAdjustable(): ?AdjustableInterface
    {
        return $this->order;
    }
}
