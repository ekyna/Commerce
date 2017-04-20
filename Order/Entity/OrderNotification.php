<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractNotification;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleNotificationInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderNotificationInterface;

/**
 * Class OrderNotification
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderNotification extends AbstractNotification implements OrderNotificationInterface
{
    protected ?OrderInterface $order = null;


    public function getSale(): ?SaleInterface
    {
        return $this->getOrder();
    }

    public function setSale(SaleInterface $sale = null): SaleNotificationInterface
    {
        if ($sale && !$sale instanceof OrderInterface) {
            throw new UnexpectedTypeException($sale, OrderInterface::class);
        }

        return $this->setOrder($sale);
    }

    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    public function setOrder(OrderInterface $order = null): OrderNotificationInterface
    {
        if ($order === $this->order) {
            return $this;
        }

        if ($previous = $this->order) {
            $this->order = null;
            $previous->removeNotification($this);
        }

        if ($this->order = $order) {
            $this->order->addNotification($this);
        }

        return $this;
    }
}
