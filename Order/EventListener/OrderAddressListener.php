<?php

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleAddressListener;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Model\OrderAddressInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderAddressListener
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderAddressListener extends AbstractSaleAddressListener
{
    /**
     * @inheritdoc
     */
    protected function dispatchSaleTaxResolutionEvent(Model\AddressInterface $address)
    {
        /** @var OrderAddressInterface $address */
        $order = $address->getOrder();

        $event = $this->dispatcher->createResourceEvent($order);

        $this->dispatcher->dispatch(OrderEvents::TAX_RESOLUTION, $event);
    }

    /**
     * @inheritdoc
     */
    protected function getAddressFromEvent(ResourceEventInterface $event)
    {
        $address = $event->getResource();

        if (!$address instanceof OrderAddressInterface) {
            throw new InvalidArgumentException("Expected instance of OrderAddressInterface.");
        }

        return $address;
    }
}
