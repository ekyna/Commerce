<?php


namespace Ekyna\Component\Commerce\Order\Generator;

use Ekyna\Component\Commerce\Order\Model\OrderInterface;

/**
 * Interface NumberGeneratorInterface
 * @package Ekyna\Component\Commerce\Order\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface NumberGeneratorInterface
{
    /**
     * Generates the order number.
     *
     * @param OrderInterface $order
     *
     * @return NumberGeneratorInterface
     */
    public function generateNumber(OrderInterface $order);

    /**
     * Generates the order key.
     *
     * @param OrderInterface $order
     *
     * @return NumberGeneratorInterface
     */
    public function generateKey(OrderInterface $order);
}
