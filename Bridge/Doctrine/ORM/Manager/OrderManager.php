<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Manager;

use Ekyna\Component\Commerce\Order\Manager\OrderManagerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ResourceManager;

/**
 * Class OrderManager
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderManager extends ResourceManager implements OrderManagerInterface
{
    use UpdateMarginTrait;

    /**
     * @inheritDoc
     */
    public function updateMargin(OrderInterface $order): void
    {
        $this->updateMarginSubject($order);
    }
}
