<?php


namespace Ekyna\Component\Commerce\Order\Resolver;

use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;

/**
 * Interface StateResolverInterface
 * @package Ekyna\Component\Commerce\Order\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StateResolverInterface
{
    /**
     * Resolves the order state.
     *
     * @param OrderInterface $order
     *
     * @return StateResolverInterface
     * @throws CommerceExceptionInterface
     */
    public function resolve(OrderInterface $order);
}
