<?php

declare(strict_types=1);

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
    protected ?OrderInterface $order = null;


    public function getSale(): ?SaleInterface
    {
        return $this->order;
    }

    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    public function setOrder(?OrderInterface $order): OrderAdjustmentInterface
    {
        if ($order === $this->order) {
            return $this;
        }

        if ($previous = $this->order) {
            $this->order = null;
            $previous->removeAdjustment($this);
        }

        if ($this->order = $order) {
            $this->order->addAdjustment($this);
        }

        return $this;
    }

    public function getAdjustable(): ?AdjustableInterface
    {
        return $this->order;
    }
}
