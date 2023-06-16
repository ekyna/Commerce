<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\MessageHandler;

use Ekyna\Component\Commerce\Order\Message\UpdateOrderMargin;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Order\Updater\OrderUpdaterInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;

/**
 * Class UpdateOrderMarginHandler
 * @package Ekyna\Component\Commerce\Order\MessageHandler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UpdateOrderMarginHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $repository,
        private readonly OrderUpdaterInterface    $updater,
        private readonly ResourceManagerInterface $manager,
    ) {
    }

    public function __invoke(UpdateOrderMargin $message): void
    {
        if (null === $order = $this->repository->find($message->orderId)) {
            return;
        }

        if (!$this->updater->updateMargin($order)) {
            return;
        }

        $this->manager->persist($order);
        $this->manager->flush();
    }
}
