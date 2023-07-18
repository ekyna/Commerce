<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\MessageHandler;

use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Manager\OrderInvoiceManagerInterface;
use Ekyna\Component\Commerce\Order\Manager\OrderManagerInterface;
use Ekyna\Component\Commerce\Order\Message\UpdateOrderMargin;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Order\Updater\OrderInvoiceUpdaterInterface;
use Ekyna\Component\Commerce\Order\Updater\OrderUpdaterInterface;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;

/**
 * Class UpdateOrderMarginHandler
 * @package Ekyna\Component\Commerce\Order\MessageHandler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UpdateOrderMarginHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface     $repository,
        private readonly OrderUpdaterInterface        $orderUpdater,
        private readonly OrderInvoiceUpdaterInterface $invoiceUpdater,
        private readonly ManagerFactoryInterface      $managerFactory,
    ) {
    }

    public function __invoke(UpdateOrderMargin $message): void
    {
        if (null === $order = $this->repository->find($message->orderId)) {
            return;
        }

        $orderManager = $this->managerFactory->getManager('ekyna_commerce.order');
        if (!$orderManager instanceof OrderManagerInterface) {
            throw new UnexpectedTypeException($orderManager, OrderManagerInterface::class);
        }

        $invoiceManager = $this->managerFactory->getManager('ekyna_commerce.order_invoice');
        if (!$invoiceManager instanceof OrderInvoiceManagerInterface) {
            throw new UnexpectedTypeException($invoiceManager, OrderInvoiceManagerInterface::class);
        }

        if ($this->orderUpdater->updateMargin($order)) {
            $orderManager->updateMargin($order);
        }

        foreach ($order->getInvoices() as $invoice) {
            if ($this->invoiceUpdater->updateMargin($invoice)) {
                $invoiceManager->updateMargin($invoice);
            }
        }
    }
}
