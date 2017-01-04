<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Order\Model;
use Ekyna\Component\Commerce\Payment\Entity\AbstractPayment;

/**
 * Class OrderPayment
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderPayment extends AbstractPayment implements Model\OrderPaymentInterface
{
    /**
     * @var Model\OrderInterface
     */
    protected $order;


    /**
     * @inheritdoc
     *
     * @return Model\OrderInterface
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
    public function setOrder(Model\OrderInterface $order = null)
    {
        if (null !== $this->order && $this->order != $order) {
            $this->order->removePayment($this);
        }

        $this->order = $order;

        return $this;
    }
}
