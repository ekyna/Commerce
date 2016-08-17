<?php

namespace Ekyna\Component\Commerce\Order\Generator;

use Ekyna\Component\Commerce\Order\Model\OrderInterface;

/**
 * Class DefaultNumberGenerator
 * @package Ekyna\Component\Commerce\Order\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DefaultNumberGenerator implements NumberGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generateNumber(OrderInterface $order)
    {
        if (null !== $order->getNumber()) {
            return $this;
        }

        // TODO read last number from a file

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function generateKey(OrderInterface $order)
    {
        if (null !== $order->getKey()) {
            return $this;
        }

        // TODO

        return $this;
    }
}
