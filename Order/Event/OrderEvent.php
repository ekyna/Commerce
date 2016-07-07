<?php

namespace Ekyna\Component\Commerce\Order\Event;

use Ekyna\Component\Commerce\Order\Model\OrderEventInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class OrderEvent
 * @package Ekyna\Component\Commerce\Order\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderEvent extends Event implements OrderEventInterface
{
    /**
     * @var OrderInterface
     */
    private $order;


    /**
     * Constructor.
     *
     * @param OrderInterface $order
     */
    public function __construct(OrderInterface $order)
    {
        $this->order = $order;
    }

    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        return $this->order;
    }
}
