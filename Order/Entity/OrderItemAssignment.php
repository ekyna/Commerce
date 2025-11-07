<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Stock\Entity\AbstractAssignment;
use Ekyna\Component\Commerce\Stock\Model\AssignableInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignmentInterface;

/**
 * Class OrderItemAssignment
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItemAssignment extends AbstractAssignment
{
    protected ?OrderItemInterface $orderItem = null;

    public function getOrderItem(): ?OrderItemInterface
    {
        return $this->orderItem;
    }

    public function setOrderItem(?OrderItemInterface $orderItem): OrderItemAssignment
    {
        if ($orderItem === $this->orderItem) {
            return $this;
        }

        if ($previous = $this->orderItem) {
            $this->orderItem = null;
            $previous->removeStockAssignment($this);
        }

        if ($this->orderItem = $orderItem) {
            $this->orderItem->addStockAssignment($this);
        }

        return $this;
    }

    public function getAssignable(): ?AssignableInterface
    {
        return $this->getOrderItem();
    }

    public function setAssignable(?AssignableInterface $assignable): AssignmentInterface
    {
        if ($assignable && !$assignable instanceof OrderItemInterface) {
            throw new UnexpectedTypeException($assignable, OrderItemInterface::class);
        }

        return $this->setOrderItem($assignable);
    }

    public function isRemovalPrevented(): bool
    {
        if (null === $order = $this->orderItem?->getRootSale()) {
            return false;
        }

        if (!OrderStates::isStockableState($order)) {
            return false;
        }

        return parent::isRemovalPrevented();
    }
}
