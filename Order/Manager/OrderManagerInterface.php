<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Manager;

use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;

/**
 * Interface OrderManagerInterface
 * @package Ekyna\Component\Commerce\Order\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface OrderManagerInterface extends ResourceManagerInterface
{
    /**
     * Updates the order invoice directly in the database (not using ORM).
     *
     * @param OrderInterface $order
     * @return void
     */
    public function updateMargin(OrderInterface $order): void;
}
