<?php

namespace Ekyna\Component\Commerce\Cart\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleListener;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class CartListener
 * @package Ekyna\Component\Commerce\Cart\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartListener extends AbstractSaleListener
{
    /**
     * @inheritdoc
     */
    protected function getSaleFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof CartInterface) {
            throw new InvalidArgumentException("Expected instance of CartInterface");
        }

        return $resource;
    }
}
