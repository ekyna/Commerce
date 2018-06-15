<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAttachment;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Order\Model\OrderAttachmentInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;

/**
 * Class OrderAttachment
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderAttachment extends AbstractAttachment implements OrderAttachmentInterface
{
    /**
     * @var OrderInterface
     */
    protected $order;


    /**
     * @inheritdoc
     */
    public function getSale()
    {
        return $this->getOrder();
    }

    /**
     * @inheritdoc
     */
    public function setSale(SaleInterface $sale = null)
    {
        return $this->setOrder($sale);
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
            if ($previous = $this->order) {
                $this->order = null;
                $previous->removeAttachment($this);
            }

            if ($this->order = $order) {
                $this->order->addAttachment($this);
            }
        }

        return $this;
    }
}
