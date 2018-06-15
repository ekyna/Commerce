<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractNotification;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderNotificationInterface;

/**
 * Class OrderNotification
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderNotification extends AbstractNotification implements OrderNotificationInterface
{
    /**
     * @var OrderInterface
     */
    protected $order;


    /**
     * @inheritDoc
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @inheritDoc
     */
    public function setOrder(OrderInterface $order = null)
    {
        if ($order !== $this->order) {
            if ($previous = $this->order) {
                $this->order = null;
                $previous->removeNotification($this);
            }

            if ($this->order = $order) {
                $this->order->addNotification($this);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSale()
    {
        return $this->getOrder();
    }

    /**
     * @inheritDoc
     */
    public function setSale(SaleInterface $sale = null)
    {
        return $this->setOrder($sale);
    }
}
