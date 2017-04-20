<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAttachment;
use Ekyna\Component\Commerce\Common\Model\SaleAttachmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Model\OrderAttachmentInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;

/**
 * Class OrderAttachment
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderAttachment extends AbstractAttachment implements OrderAttachmentInterface
{
    protected ?OrderInterface $order = null;


    public function getSale(): ?SaleInterface
    {
        return $this->getOrder();
    }

    public function setSale(SaleInterface $sale = null): SaleAttachmentInterface
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

    public function setOrder(?OrderInterface $order): OrderAttachmentInterface
    {
        if ($order === $this->order) {
            return $this;
        }

        if ($previous = $this->order) {
            $this->order = null;
            $previous->removeAttachment($this);
        }

        if ($this->order = $order) {
            $this->order->addAttachment($this);
        }

        return $this;
    }
}
